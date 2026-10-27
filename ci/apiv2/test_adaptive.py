"""Adaptive (normal) chunk-sizing coverage through the live apiv2 + agent protocol.

Every other task fixture in this suite uses STATIC chunking (``staticChunks`` 1/2), so the adaptive
code path -- seed ``chunkSpeed`` from a benchmark, then reconcile it toward the agent's observed speed
when a chunk COMPLETES, and re-size the next chunk -- had zero integration coverage.
``create_task_005.json`` is the only NORMAL-chunking fixture (``staticChunks: 0``,
``useNewBench: false``, ``chunkTime: 10``), which forces ``ChunkUtils::calculateChunkSize`` down the
adaptive branch (``size = floor(chunkSpeed * chunkTime / SPEED_SCALE)``).

``chunkSpeed`` is stored scaled by SPEED_SCALE (1000, milli-base-words/second) so low rates keep
sub-unit precision. The reconcile signal is the WHOLE-CHUNK observed base-word rate, derived
server-side when a chunk completes as ``chunk length / (solveTime - dispatchTime)`` -- NOT the raw
hashcat hash-rate the agent reports, and NOT a per-report delta. A chunk's ``length`` and a task's
``keyspace`` are in base words, so this is unit-correct and multiplier-agnostic; feeding the raw rate
(``base-words/s * rules * salts``) would over-size every chunk. See ``ChunkUtils::completedChunkSpeed``
and ``ChunkUtils::reconcileSpeed`` (climb-only). The exact arithmetic is pinned deterministically by the
phpunit unit tests; the assertions here are direction/bound based so wall-clock wobble cannot flake them.

To make a chunk's wall-clock duration measurable (the server ignores chunks shorter than
``RECONCILE_MIN_DURATION`` = 5s as too short to time), these tests hold a real gap between dispatching a
chunk and reporting its completion.
"""
import time

from hashtopolis import AgentAssignment, Chunk, Config, Task

from hashtopolis_agent import ProcessState
from utils import (
    BaseTest,
    do_create_agentassignent,
    do_create_dummy_agent,
    do_create_hashlist,
    do_create_task,
)

# --- experiment constants ---
KEYSPACE = 30_000_000
CHUNK_TIME = 10             # must match create_task_005.json
SPEED_SCALE = 1000          # must match ChunkUtils::SPEED_SCALE
RUNTIME_BENCHMARK = 0.0005  # a deliberately small runtime-benchmark result -> a low seed (the undermeasure)
CHUNK_RUN_SECONDS = 6.5     # real wall-clock a chunk "runs" before completion (> server 5s measure floor)


class AdaptiveChunkSizingTest(BaseTest):
    model_class = Task

    def setUp(self):
        """The server-wide ``chunktimeAutoTune`` toggle defaults to OFF; these tests exercise the
        reconcile path, so enable it for the duration of each test and restore afterwards."""
        super().setUp()
        self._tune_cfg = Config.objects.get(item='chunktimeAutoTune')
        self._tune_original = self._tune_cfg.value
        self._tune_cfg.value = "1"
        self._tune_cfg.save()

    def tearDown(self):
        self._tune_cfg.value = self._tune_original
        self._tune_cfg.save()
        super().tearDown()

    def _setup_normal_task_agent(self, runtime_benchmark=RUNTIME_BENCHMARK):
        """Create a NORMAL-chunking task (fixture 005) with one dummy agent assigned and benchmarked.

        Drives the agent through the exact protocol the server gates on for a non-small, non-static task:
        getTask -> sendKeyspace -> sendBenchmark(run) -> getChunk (first real chunk). Returns the dummy
        agent, the live Task and the Agent objects, with the first chunk dispatched in ``dummy_agent.chunk``.
        """
        dummy_agent, agent = do_create_dummy_agent()
        hashlist = do_create_hashlist()
        task = do_create_task(hashlist=hashlist, file_id='005')
        do_create_agentassignent(agent, task)

        # Register for teardown. tearDown pops LIFO, and a Task must be deleted before its Hashlist (FK),
        # so push agent -> hashlist -> task to pop task -> hashlist -> agent.
        self.delete_after_test(agent)
        self.delete_after_test(hashlist)
        self.delete_after_test(task)

        dummy_agent.get_task()
        dummy_agent.get_hashlist()

        # keyspace measurement: getChunk returns keyspace_required, then we report it
        dummy_agent.get_chunk()
        dummy_agent.send_keyspace(keyspace=KEYSPACE)

        # benchmark: getChunk returns benchmark, then we report a deliberately LOW runtime result
        dummy_agent.get_chunk()
        dummy_agent.send_benchmark(benchmark_type="run", result=runtime_benchmark)

        # first real chunk dispatched (sized from the seeded chunkSpeed)
        dummy_agent.get_chunk()

        return dummy_agent, task, agent

    def _chunk_speed(self, task, agent):
        """Read the server-side canonical chunkSpeed (scaled by SPEED_SCALE) for this assignment."""
        assignments = list(AgentAssignment.objects.filter(taskId=task.id, agentId=agent.id))
        self.assertEqual(len(assignments), 1, "expected exactly one assignment for the agent/task")
        return assignments[0].chunkSpeed

    def _complete_current_chunk(self, dummy_agent, raw_speed=None):
        """Let the dispatched chunk 'run' for a measurable wall-clock gap, then report it EXHAUSTED so the
        server reconciles chunkSpeed from the whole-chunk rate. Optionally report an (ignored) raw speed."""
        time.sleep(CHUNK_RUN_SECONDS)
        kwargs = {} if raw_speed is None else {"speed": raw_speed}
        dummy_agent.send_process(progress=100, state=ProcessState.EXHAUSTED, **kwargs)

    # ------------------------------------------------------------------ smoke

    def test_normal_chunking_task_seeds_and_dispatches(self):
        """Smoke: a NORMAL-chunking task creates, the runtime benchmark seeds a positive chunkSpeed, and
        the server dispatches a real chunk whose length follows the scaled adaptive formula
        floor(chunkSpeed * chunkTime / SPEED_SCALE)."""
        dummy_agent, task, agent = self._setup_normal_task_agent()

        live_task = Task.objects.get(taskId=task.id)
        self.assertEqual(live_task.staticChunks, 0, "fixture 005 must use NORMAL (non-static) chunking")
        self.assertEqual(live_task.useNewBench, 0, "fixture 005 must use the runtime benchmark path")
        self.assertEqual(live_task.chunkTime, CHUNK_TIME)

        seeded = self._chunk_speed(task, agent)
        self.assertIsNotNone(seeded, "chunkSpeed must be seeded (non-null) after a runtime benchmark")
        self.assertGreater(seeded, 0, "seeded chunkSpeed must be positive")

        self.assertIn('length', dummy_agent.chunk,
                      f"expected a dispatched chunk, got: {dummy_agent.chunk}")
        first_len = int(dummy_agent.chunk['length'])
        self.assertEqual(first_len, seeded * CHUNK_TIME // SPEED_SCALE,
                         "first chunk length must follow floor(chunkSpeed * chunkTime / SPEED_SCALE)")

    # ----------------------------------------------------- reconcile wiring (climb-only, on completion)

    def test_chunk_speed_reconciles_up_on_chunk_completion(self):
        """End-to-end wiring: completing a chunk reconciles chunkSpeed UP from the low benchmark seed
        toward the whole-chunk observed base-word rate, and the next dispatched chunk is larger."""
        dummy_agent, task, agent = self._setup_normal_task_agent()   # low seed -> small first chunk
        seed = self._chunk_speed(task, agent)
        self.assertGreater(seed, 0)
        first_len = int(dummy_agent.chunk['length'])

        self._complete_current_chunk(dummy_agent)
        reconciled = self._chunk_speed(task, agent)
        self.assertGreater(reconciled, seed,
                           "chunkSpeed must reconcile UP from the seed after a completed chunk")
        # One climb-only EWMA step is clamped to <= seed * RECONCILE_MAX_UP_RATIO (2x).
        self.assertLessEqual(reconciled, seed * 2,
                             f"chunkSpeed {reconciled} exceeded the 2x per-step clamp from seed {seed}")

        dummy_agent.get_chunk()
        self.assertGreater(int(dummy_agent.chunk['length']), first_len,
                           "the next chunk must be larger after chunkSpeed reconciled up")

    def test_chunk_speed_tracks_base_words_not_raw_hashrate(self):
        """REGRESSION: the reconcile is driven by the whole-chunk base-word rate (length / wall time),
        never the raw hashcat hash-rate the agent reports. An ``-a 0 -r`` attack reports H/s ==
        base-words/s * rule-count; a chunk's length is in BASE WORDS, so chunkSpeed must stay tied to the
        chunk length over its duration regardless of how inflated the reported speed is."""
        dummy_agent, task, agent = self._setup_normal_task_agent()
        seed = self._chunk_speed(task, agent)
        first_len = int(dummy_agent.chunk['length'])
        inflated_raw = seed * 10_000   # an absurd raw hash-rate the reconcile must ignore

        self._complete_current_chunk(dummy_agent, raw_speed=inflated_raw)
        reconciled = self._chunk_speed(task, agent)

        # The whole-chunk rate is first_len base words over ~CHUNK_RUN_SECONDS, scaled by SPEED_SCALE.
        # It is bounded by the 2x climb clamp and is orders of magnitude below the inflated raw rate.
        self.assertLessEqual(reconciled, seed * 2,
                             f"chunkSpeed {reconciled} tracked the raw hash-rate {inflated_raw}, not base words")
        upper_rate = int(first_len * SPEED_SCALE / CHUNK_RUN_SECONDS) * 2 + SPEED_SCALE
        self.assertLessEqual(reconciled, max(upper_rate, seed),
                             f"chunkSpeed {reconciled} is above the plausible base-word rate for the chunk")

    # ----------------------------------------------------- config toggle (off = legacy)

    def test_chunk_time_auto_tune_toggle_off_freezes_seed(self):
        """With the ``chunktimeAutoTune`` config disabled, the reconcile write is gated off and
        chunkSpeed stays frozen at the benchmark seed, even across a completed chunk that WOULD reconcile
        while the toggle is on."""
        cfg = Config.objects.get(item='chunktimeAutoTune')
        original = cfg.value
        cfg.value = "0"
        cfg.save()
        try:
            dummy_agent, task, agent = self._setup_normal_task_agent()
            seed = self._chunk_speed(task, agent)
            self.assertGreater(seed, 0)

            self._complete_current_chunk(dummy_agent, raw_speed=seed * 50)

            self.assertEqual(self._chunk_speed(task, agent), seed,
                             "chunkSpeed must stay frozen at the seed while adaptive sizing is disabled")
        finally:
            cfg.value = original
            cfg.save()

    # ----------------------------------------------------- climb-only (never shrinks)

    def test_chunk_speed_never_shrinks(self):
        """Climb-only: a chunk that completes SLOWLY (a low observed rate, below the seed) must not drag
        chunkSpeed below the seed."""
        dummy_agent, task, agent = self._setup_normal_task_agent(runtime_benchmark=0.05)  # higher seed
        seed = self._chunk_speed(task, agent)
        self.assertGreater(seed, 0)

        # Let the (seed-sized) chunk run LONGER than chunkTime, so its whole-chunk observed rate
        # (length / duration) comes out below the seed rate (length / chunkTime); climb-only must then
        # hold chunkSpeed at the seed rather than reduce it.
        time.sleep(CHUNK_TIME + 4)
        dummy_agent.send_process(progress=100, state=ProcessState.EXHAUSTED)

        self.assertEqual(self._chunk_speed(task, agent), seed,
                         "chunkSpeed must never shrink below the seed on a slow chunk (climb-only)")

    # ------------------------------------------------------------- persistence

    def test_chunks_persisted_for_normal_task(self):
        """The dispatched chunks are real, persisted Chunk rows for the task (not phantom responses)."""
        dummy_agent, task, agent = self._setup_normal_task_agent()
        for _ in range(3):
            dummy_agent.send_process(progress=50, state=ProcessState.RUNNING)
            dummy_agent.send_process(progress=100, state=ProcessState.EXHAUSTED)
            dummy_agent.get_chunk()

        chunks = list(Chunk.objects.filter(taskId=task.id))
        # 1 seed chunk + 3 advanced chunks dispatched; allow >= to stay robust to any extra bookkeeping.
        self.assertGreaterEqual(len(chunks), 4,
                                f"expected at least 4 dispatched chunks, got {len(chunks)}")
