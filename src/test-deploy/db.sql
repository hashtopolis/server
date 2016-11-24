
-- Example users, all have the same password 'test'
INSERT INTO `User` (`userId`, `username`, `passwordHash`, `email`, `passwordSalt`, `isValid`, `isComputedPassword`, `lastLoginDate`, `registeredSince`, `sessionLifetime`, `rightGroupId`) VALUES
  (1, 'test1', '$2y$12$uqRIFszBYnEDQjCxf0lUse5XblUYM1Ns2ikbmyNm4Vb/FGVvJO7bm', 'test@test.test', 'ezjD47pQO8HQK2f8cw22', 1, 0, 1479987528, 1479974642, 600, 5),
  (2, 'test2', '$2y$12$4heZea1LD0Olrc70EjBmSuqUFnFLBYOEKN42L9HPB8kms52J5KT1W', 'test@test.test', 'pzmFehQnbNTiehTrWp3v', 1, 0, 1479987649, 1479974642, 600, 4),
  (3, 'test3', '$2y$12$5QdRKCeTJvpgRHLar4oPte2tNydQEqJMtztCBtRwe0B.rr2sUUEhe', 'test@test.test', 'R3yhG4ib4w9cpj5QnH6v', 1, 0, 1479987681, 1479974642, 600, 3),
  (4, 'test4', '$2y$12$2qq7TvW6WfkA.l191BiNVun4nqkZPJHvRMNUNJFexiqvLbo1jcJqm', 'test@test.test', '0OYP4nCaDynksLrYGA0A', 1, 0, 1479987719, 1479974642, 600, 2),
  (5, 'test5', '$2y$12$7vLWGL/8XFQPiEZ13Jyjo.hcjniGNA10phEVb39tI7A8SfQHo4Jom', 'test@test.test', 'vrHOlPGxTI2JEHCq8Q3u', 1, 0, 1479987743, 1479974642, 600, 1);


INSERT INTO `Agent` (`agentId`, `agentName`, `uid`, `os`, `gpuDriver`, `gpus`, `hcVersion`, `cmdPars`, `wait`, `ignoreErrors`, `isActive`, `isTrusted`, `token`, `lastAct`, `lastTime`, `lastIp`, `userId`, `cpuOnly`) VALUES
  (3, 'test-agent', 'a9b3485b-0559-45e8-9358-ed0e51af92c7', 0, 0, 'ATI Radeon HD7970\nATI Radeon HD7970\nATI Radeon HD7970\nATI Radeon HD7970', '3.1', '--gpu-temp-abort=100', 0, 0, 0, 1, 'JGFE87gus', 0, 0, '1.2.3.4', NULL, 0),
  (15, 'cracky', 'BADID_731171', 1, 1912, 'Advanced Micro Devices, Inc. [AMD/ATI] Tahiti XT [Radeon HD 7970/8970 OEM / R9 280X]\nAdvanced Micro Devices, Inc. [AMD/ATI] Tahiti XT [Radeon HD 7970/8970 OEM / R9 280X]\nAdvanced Micro Devices, Inc. [AMD/ATI] Tahiti XT [Radeon HD 7970/8970 OEM / R9 280X]\nAdvanced Micro Devices, Inc. [AMD/ATI] Tahiti XT [Radeon HD 7970/8970 OEM / R9 280X]', '3.00', '--gpu-temp-abort=100', 0, 0, 0, 1, 'VlpVJ1VPuX', 'task', 1472543625, '2.5.4.1', NULL, 2),
  (35, '-', '0656C7D6', 0, 1507, 'AMD Radeon R9 200 SeriesAMD Radeon R9 200 SeriesAMD Radeon R9 200 / HD 7900 SeriesAMD Radeon R9 200 Series', '2.01', '-w 3 --weak-hash-threshold=1', 0, 0, 0, 0, 'ZPhPYz740K', 'down', 1465935097, '7.6.5.4', NULL, 5),
  (36, 'Agent 86', 'EEDDB978', 0, 1502, 'AMD FirePro W4100 (FireGL V) Graphics Adapter', '2.01', '--workload-profile=2 --weak-hash-threshold=1', 0, 0, 1, 1, 'Ety5rUtWfv', 'solve', 1465619104, '9.9.9.9', NULL, 6),
  (40, 'Agent 99', '02a35a81-6caa-4c66-8a2a-812b4526803c', 1, 35263, 'NVIDIA Corporation Device 17c8 (rev a1)\nNVIDIA Corporation Device 13c2 (rev a1)', '2.01', '--workload-profile=2 --weak-hash-threshold=1', 0, 0, 1, 1, 's3mV2cQGXQ', 'solve', 1465877539, '1.1.1.1', NULL, 6),
  (48, 'BLAZER-PC', '885075B5', 0, 1508, 'Intel(R) HD Graphics 4000AMD Radeon R9 200 / HD 7900 SeriesAMD Radeon R9 200 Series', '2.01', '--weak-hash-threshold=1  --gpu-temp-disable -w 2', 0, 0, 1, 1, 'zj0Tn02oqI', 'solve', 1465894294, '1.2.3.4', NULL, 7),
  (49, 'RIG13-PC', '3E69452E', 0, 1508, 'AMD Radeon R9 200 SeriesAMD Radeon R9 200 SeriesAMD Radeon R9 200 Series', '2.01', '--weak-hash-threshold=1', 0, 0, 0, 1, '4zWbMxJveh', 'task', 1465246944, '6.7.8.9', NULL, 8),
  (53, 'REDQUEEN-PC', 'E8F74057', 0, 36881, 'NVIDIA GeForce GTX 980NVIDIA GeForce GTX 980', '3.00', NULL, 0, 0, 1, 0, 'VNBbTaD598', 'down', 1472561712, '9.1.2.4', NULL, 0),
  (55, 'SUPERUSER', '4C40DA59', 0, 36839, 'NVIDIA GeForce GTX 750 Ti', '2.01', NULL, 0, 0, 1, 1, 'koEC9yL65a', 'solve', 1465508213, '213.188.227.30', NULL, 2),
  (57, 'TV-PC', '86C41D81', 0, 1508, 'AMD Radeon R9 200 Series', '2.01', '-w 3 --weak-hash-threshold=1', 0, 0, 1, 0, 'hKluRqdrlJ', 'solve', 1465948147, '5.2.5.3', NULL, 0),
  (58, 'HASHCATMINI', '0E9761B1', 0, 1507, 'AMD Radeon HD 7800 SeriesAMD Radeon HD 7800 Series', '2.01', '-w 3 --weak-hash-threshold=1', 0, 0, 1, 0, 'byzHpWSZlH', 'solve', 1465948148, '6.7.8.10', NULL, 0),
  (59, '6x7950', 'BADID_29455', 1, 1507, 'Advanced Micro Devices, Inc. [AMD/ATI] Tahiti PRO [Radeon HD 7950/8950 OEM / R9 280]Advanced Micro Devices, Inc. [AMD/ATI] Tahiti PRO [Radeon HD 7950/8950 OEM / R9 280]Advanced Micro Devices, Inc. [AMD/ATI] Tahiti PRO [Radeon HD 7950/8950 OEM / R9 280]Advanced Micro Devices, Inc. [AMD/ATI] Tahiti PRO [Radeon HD 7950/8950 OEM / R9 280]Advanced Micro Devices, Inc. [AMD/ATI] Tahiti PRO [Radeon HD 7950/8950 OEM / R9 280]Advanced Micro Devices, Inc. [AMD/ATI] Tahiti PRO [Radeon HD 7950/8950 OEM / R9 280]', '2.01', '-w 3 --gpu-temp-disable --weak-hash-threshold=1', 0, 0, 1, 0, 's07P3IxUp3', 'solve', 1465948148, '45.23.45.65', NULL, 0),
  (61, 'HASHCAT2', '8E5FEC61', 0, 1508, 'AMD Radeon R9 200 SeriesAMD Radeon R9 200 SeriesAMD Radeon R9 200 SeriesAMD Radeon R9 200 / HD 7900 Series', '2.01', '-w 3 --weak-hash-threshold=1', 0, 0, 1, 0, '73TR6fptlq', 'solve', 1465948151, '34.22.35.57', NULL, 0),
  (62, '7970-PC', '6E786513', 0, 1508, 'AMD Radeon R9 200 SeriesAMD Radeon R9 200 / HD 7900 SeriesAMD Radeon R9 200 Series', '2.01', '--weak-hash-threshold=1', 0, 0, 1, 0, 'KZbD4utTnk', 'solve', 1465948148, '1.43.6.34', NULL, 0),
  (63, 'REDQUEEN-PC', 'E8F74057', 0, 36472, 'NVIDIA GeForce GTX 980NVIDIA GeForce GTX 980', '2.01', '-w 1 --weak-hash-threshold=1', 0, 0, 1, 0, 'rJOZXLxDlP', 'solve', 1465948144, '1.4.3.45', NULL, 0),
  (64, 'LUCKY-PC', '2A9E5A72', 0, 1409, 'AMD Radeon R9 200 SeriesAMD Radeon R9 200 Series', '2.01', '-d 2', 0, 0, 1, 1, 'A1PE8t7JuM', 'solve', 1465896597, '32.4.43.2', NULL, 4),
  (65, 'LUC-PC', '4CFA03A5', 0, 35362, 'NVIDIA GeForce GTX 570NVIDIA GeForce GTX 970', '2.01', '-d 1', 0, 0, 1, 1, 'C72uJkrzRN', 'solve', 1465896297, '1.5.5.5', NULL, 4),
  (66, 'LUC3-PC', 'CE488991', 0, 1507, 'AMD Radeon HD 6900 SeriesAMD Radeon HD 6900 SeriesAMD Radeon HD 6900 Series', '2.01', '-d 1,2', 0, 0, 0, 1, '093zUpwOo2', 'err', 1465876937, '5.5.5.4', NULL, 4),
  (67, 'LUC3-PC', 'CE488991', 0, 1507, 'AMD Radeon HD 6900 SeriesAMD Radeon HD 6900 SeriesAMD Radeon HD 6900 Series', '2.01', NULL, 0, 0, 0, 1, 'UtkuuorZj7', 'task', 1465936225, '6.4.6.4', NULL, 4),
  (68, '-', '0656C7D6', 0, 1507, 'AMD Radeon R9 200 SeriesAMD Radeon R9 200 / HD 7900 SeriesAMD Radeon R9 200 SeriesAMD Radeon R9 200 Series', '2.01', '-w 3 --weak-hash-threshold=1', 0, 0, 1, 0, '9L40EimFXH', 'solve', 1466362157, '5.6.3.4', NULL, 5),
  (69, 'Jetstream', 'BADID_889932', 1, 1511, 'Advanced Micro Devices, Inc. [AMD/ATI] Hawaii PRO [Radeon R9 290]Advanced Micro Devices, Inc. [AMD/ATI] Hawaii XT [Radeon R9 290X]Advanced Micro Devices, Inc. [AMD/ATI] Hawaii PRO [Radeon R9 290]', '2.01', '-w 3', 0, 0, 0, 1, 'KOGAC17HpB', 'task', 1469901362, '8.7.8.8', NULL, 9);

INSERT INTO `Hashlist` (`hashlistId`, `hashlistName`, `format`, `hashTypeId`, `hashCount`, `saltSeparator`, `cracked`, `secret`, `hexSalt`) VALUES
  (NULL, 'test-list', '0', '10', '100', '', '0', '0', '0');

INSERT INTO `Task` (`taskId`, `taskName`, `attackCmd`, `hashlistId`, `chunkTime`, `statusTimer`, `autoAdjust`, `keyspace`, `progress`, `priority`, `color`, `isSmall`, `isCpuTask`) VALUES
  (NULL, 'test-task', '#HL# -a 0', '1', '', '', '', '100000', '123456', '0', '', '0', '0');