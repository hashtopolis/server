-- initial db for this generation

--
-- Name: accessgroup; Type: TABLE;
--

CREATE TABLE accessgroup (
    accessgroupid integer NOT NULL,
    groupname text NOT NULL
);

--
-- Name: accessgroup_accessgroupid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE accessgroup_accessgroupid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: accessgroupagent; Type: TABLE;
--

CREATE TABLE accessgroupagent (
    accessgroupagentid integer NOT NULL,
    accessgroupid integer NOT NULL,
    agentid integer NOT NULL
);

--
-- Name: accessgroupagent_accessgroupagentid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE accessgroupagent_accessgroupagentid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: accessgroupuser; Type: TABLE;
--

CREATE TABLE accessgroupuser (
    accessgroupuserid integer NOT NULL,
    accessgroupid integer NOT NULL,
    userid integer NOT NULL
);

--
-- Name: accessgroupuser_accessgroupuserid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE accessgroupuser_accessgroupuserid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: agent; Type: TABLE;
--

CREATE TABLE agent (
    agentid integer NOT NULL,
    agentname text NOT NULL,
    uid text NOT NULL,
    os integer NOT NULL,
    devices text NOT NULL,
    cmdpars text NOT NULL,
    ignoreerrors integer NOT NULL,
    isactive integer NOT NULL,
    istrusted integer NOT NULL,
    token text NOT NULL,
    lastact text NOT NULL,
    lasttime bigint NOT NULL,
    lastip text NOT NULL,
    userid integer,
    cpuonly integer NOT NULL,
    clientsignature text NOT NULL
);

--
-- Name: agent_agentid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE agent_agentid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: agentbinary; Type: TABLE;
--

CREATE TABLE agentbinary (
    agentbinaryid integer NOT NULL,
    binarytype text NOT NULL,
    version text NOT NULL,
    operatingsystems text NOT NULL,
    filename text NOT NULL,
    updatetrack text NOT NULL,
    updateavailable text NOT NULL
);

--
-- Name: agentbinary_agentbinaryid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE agentbinary_agentbinaryid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: agenterror; Type: TABLE;
--

CREATE TABLE agenterror (
    agenterrorid integer NOT NULL,
    agentid integer NOT NULL,
    taskid integer,
    "time" bigint NOT NULL,
    error text NOT NULL,
    chunkid integer
);

--
-- Name: agenterror_agenterrorid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE agenterror_agenterrorid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: agentstat; Type: TABLE;
--

CREATE TABLE agentstat (
    agentstatid bigint NOT NULL,
    agentid integer NOT NULL,
    stattype integer NOT NULL,
    "time" bigint NOT NULL,
    value text NOT NULL
);

--
-- Name: agentstat_agentstatid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE agentstat_agentstatid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: agentzap; Type: TABLE;
--

CREATE TABLE agentzap (
    agentzapid integer NOT NULL,
    agentid integer NOT NULL,
    lastzapid integer
);

--
-- Name: agentzap_agentzapid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE agentzap_agentzapid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: apigroup; Type: TABLE;
--

CREATE TABLE apigroup (
    apigroupid integer NOT NULL,
    name text NOT NULL,
    permissions text NOT NULL
);

--
-- Name: apigroup_apigroupid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE apigroup_apigroupid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: apikey; Type: TABLE;
--

CREATE TABLE apikey (
    apikeyid integer NOT NULL,
    startvalid bigint NOT NULL,
    endvalid bigint NOT NULL,
    accesskey text NOT NULL,
    accesscount integer NOT NULL,
    userid integer NOT NULL,
    apigroupid integer NOT NULL
);

--
-- Name: apikey_apikeyid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE apikey_apikeyid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: assignment; Type: TABLE;
--

CREATE TABLE assignment (
    assignmentid integer NOT NULL,
    taskid integer NOT NULL,
    agentid integer NOT NULL,
    benchmark text NOT NULL
);

--
-- Name: assignment_assignmentid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE assignment_assignmentid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: chunk; Type: TABLE;
--

CREATE TABLE chunk (
    chunkid integer NOT NULL,
    taskid integer NOT NULL,
    skip bigint NOT NULL,
    length bigint NOT NULL,
    agentid integer,
    dispatchtime bigint NOT NULL,
    solvetime bigint NOT NULL,
    checkpoint bigint NOT NULL,
    progress integer,
    state integer NOT NULL,
    cracked integer NOT NULL,
    speed bigint NOT NULL
);

--
-- Name: chunk_chunkid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE chunk_chunkid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: config; Type: TABLE;
--

CREATE TABLE config (
    configid integer NOT NULL,
    configsectionid integer NOT NULL,
    item text NOT NULL,
    value text NOT NULL
);

--
-- Name: config_configid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE config_configid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: configsection; Type: TABLE;
--

CREATE TABLE configsection (
    configsectionid integer NOT NULL,
    sectionname text NOT NULL
);

--
-- Name: configsection_configsectionid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE configsection_configsectionid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: crackerbinary; Type: TABLE;
--

CREATE TABLE crackerbinary (
    crackerbinaryid integer NOT NULL,
    crackerbinarytypeid integer NOT NULL,
    version text NOT NULL,
    downloadurl text NOT NULL,
    binaryname text NOT NULL
);

--
-- Name: crackerbinary_crackerbinaryid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE crackerbinary_crackerbinaryid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: crackerbinarytype; Type: TABLE;
--

CREATE TABLE crackerbinarytype (
    crackerbinarytypeid integer NOT NULL,
    typename text NOT NULL,
    ischunkingavailable integer NOT NULL
);

--
-- Name: crackerbinarytype_crackerbinarytypeid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE crackerbinarytype_crackerbinarytypeid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: file; Type: TABLE;
--

CREATE TABLE file (
    fileid integer NOT NULL,
    filename text NOT NULL,
    size bigint NOT NULL,
    issecret integer NOT NULL,
    filetype integer NOT NULL,
    accessgroupid integer NOT NULL,
    linecount bigint
);

--
-- Name: file_fileid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE file_fileid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: filedelete; Type: TABLE;
--

CREATE TABLE filedelete (
    filedeleteid integer NOT NULL,
    filename text NOT NULL,
    "time" bigint NOT NULL
);

--
-- Name: filedelete_filedeleteid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE filedelete_filedeleteid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: filedownload; Type: TABLE;
--

CREATE TABLE filedownload (
    filedownloadid integer NOT NULL,
    "time" bigint NOT NULL,
    fileid integer NOT NULL,
    status integer NOT NULL
);

--
-- Name: filedownload_filedownloadid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE filedownload_filedownloadid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: filepretask; Type: TABLE;
--

CREATE TABLE filepretask (
    filepretaskid integer NOT NULL,
    fileid integer NOT NULL,
    pretaskid integer NOT NULL
);

--
-- Name: filepretask_filepretaskid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE filepretask_filepretaskid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: filetask; Type: TABLE;
--

CREATE TABLE filetask (
    filetaskid integer NOT NULL,
    fileid integer NOT NULL,
    taskid integer NOT NULL
);

--
-- Name: filetask_filetaskid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE filetask_filetaskid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: hash; Type: TABLE;
--

CREATE TABLE hash (
    hashid integer NOT NULL,
    hashlistid integer NOT NULL,
    hash text NOT NULL,
    salt text,
    plaintext text,
    timecracked bigint,
    chunkid integer,
    iscracked integer NOT NULL,
    crackpos bigint NOT NULL
);

--
-- Name: hash_hashid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE hash_hashid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: hashbinary; Type: TABLE;
--

CREATE TABLE hashbinary (
    hashbinaryid integer NOT NULL,
    hashlistid integer NOT NULL,
    essid text NOT NULL,
    hash text NOT NULL,
    plaintext text,
    timecracked bigint,
    chunkid integer,
    iscracked integer NOT NULL,
    crackpos bigint NOT NULL
);

--
-- Name: hashbinary_hashbinaryid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE hashbinary_hashbinaryid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: hashlist; Type: TABLE;
--

CREATE TABLE hashlist (
    hashlistid integer NOT NULL,
    hashlistname text NOT NULL,
    format integer NOT NULL,
    hashtypeid integer NOT NULL,
    hashcount integer NOT NULL,
    saltseparator text,
    cracked integer NOT NULL,
    issecret integer NOT NULL,
    hexsalt integer NOT NULL,
    issalted integer NOT NULL,
    accessgroupid integer NOT NULL,
    notes text NOT NULL,
    brainid integer NOT NULL,
    brainfeatures integer NOT NULL,
    isarchived integer NOT NULL
);

--
-- Name: hashlist_hashlistid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE hashlist_hashlistid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: hashlisthashlist; Type: TABLE;
--

CREATE TABLE hashlisthashlist (
    hashlisthashlistid integer NOT NULL,
    parenthashlistid integer NOT NULL,
    hashlistid integer NOT NULL
);

--
-- Name: hashlisthashlist_hashlisthashlistid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE hashlisthashlist_hashlisthashlistid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: hashtype; Type: TABLE;
--

CREATE TABLE hashtype (
    hashtypeid integer NOT NULL,
    description text NOT NULL,
    issalted integer NOT NULL,
    isslowhash integer NOT NULL
);

--
-- Name: hashtype_hashtypeid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE hashtype_hashtypeid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: healthcheck; Type: TABLE;
--

CREATE TABLE healthcheck (
    healthcheckid integer NOT NULL,
    "time" bigint NOT NULL,
    status integer NOT NULL,
    checktype integer NOT NULL,
    hashtypeid integer NOT NULL,
    crackerbinaryid integer NOT NULL,
    expectedcracks integer NOT NULL,
    attackcmd text NOT NULL
);

--
-- Name: healthcheck_healthcheckid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE healthcheck_healthcheckid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: healthcheckagent; Type: TABLE;
--

CREATE TABLE healthcheckagent (
    healthcheckagentid integer NOT NULL,
    healthcheckid integer NOT NULL,
    agentid integer NOT NULL,
    status integer NOT NULL,
    cracked integer NOT NULL,
    numgpus integer NOT NULL,
    start bigint NOT NULL,
    htp_end bigint NOT NULL,
    errors text NOT NULL
);

--
-- Name: healthcheckagent_healthcheckagentid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE healthcheckagent_healthcheckagentid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: htp_user; Type: TABLE;
--

CREATE TABLE htp_user (
    userid integer NOT NULL,
    username text NOT NULL,
    email text NOT NULL,
    passwordhash text NOT NULL,
    passwordsalt text NOT NULL,
    isvalid integer NOT NULL,
    iscomputedpassword integer NOT NULL,
    lastlogindate bigint NOT NULL,
    registeredsince bigint NOT NULL,
    sessionlifetime integer NOT NULL,
    rightgroupid integer NOT NULL,
    yubikey text,
    otp1 text,
    otp2 text,
    otp3 text,
    otp4 text
);

--
-- Name: htp_user_userid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE htp_user_userid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: jwtapikey; Type: TABLE;
--

CREATE TABLE jwtapikey (
    jwtapikeyid integer NOT NULL,
    userid integer,
    startvalid bigint NOT NULL,
    endvalid bigint NOT NULL,
    isrevoked boolean DEFAULT false NOT NULL
);

--
-- Name: jwtapikey_jwtapikeyid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE jwtapikey_jwtapikeyid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: logentry; Type: TABLE;
--

CREATE TABLE logentry (
    logentryid bigint NOT NULL,
    issuer text NOT NULL,
    issuerid text NOT NULL,
    level text NOT NULL,
    message text NOT NULL,
    "time" bigint NOT NULL
);

--
-- Name: logentry_logentryid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE logentry_logentryid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: notificationsetting; Type: TABLE;
--

CREATE TABLE notificationsetting (
    notificationsettingid integer NOT NULL,
    action text NOT NULL,
    objectid integer,
    notification text NOT NULL,
    userid integer NOT NULL,
    receiver text NOT NULL,
    isactive integer NOT NULL
);

--
-- Name: notificationsetting_notificationsettingid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE notificationsetting_notificationsettingid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: preprocessor; Type: TABLE;
--

CREATE TABLE preprocessor (
    preprocessorid integer NOT NULL,
    name text NOT NULL,
    url text NOT NULL,
    binaryname text NOT NULL,
    keyspacecommand text,
    skipcommand text,
    limitcommand text
);

--
-- Name: preprocessor_preprocessorid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE preprocessor_preprocessorid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: pretask; Type: TABLE;
--

CREATE TABLE pretask (
    pretaskid integer NOT NULL,
    taskname text NOT NULL,
    attackcmd text NOT NULL,
    chunktime integer NOT NULL,
    statustimer integer NOT NULL,
    color text,
    issmall integer NOT NULL,
    iscputask integer NOT NULL,
    usenewbench integer NOT NULL,
    priority integer NOT NULL,
    maxagents integer NOT NULL,
    ismaskimport integer NOT NULL,
    crackerbinarytypeid integer NOT NULL
);

--
-- Name: pretask_pretaskid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE pretask_pretaskid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: regvoucher; Type: TABLE;
--

CREATE TABLE regvoucher (
    regvoucherid integer NOT NULL,
    voucher text NOT NULL,
    "time" bigint NOT NULL
);

--
-- Name: regvoucher_regvoucherid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE regvoucher_regvoucherid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: rightgroup; Type: TABLE;
--

CREATE TABLE rightgroup (
    rightgroupid integer NOT NULL,
    groupname text NOT NULL,
    permissions text NOT NULL
);

--
-- Name: rightgroup_rightgroupid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE rightgroup_rightgroupid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: session; Type: TABLE;
--

CREATE TABLE session (
    sessionid integer NOT NULL,
    userid integer NOT NULL,
    sessionstartdate bigint NOT NULL,
    lastactiondate bigint NOT NULL,
    isopen integer NOT NULL,
    sessionlifetime integer NOT NULL,
    sessionkey text NOT NULL
);

--
-- Name: session_sessionid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE session_sessionid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: speed; Type: TABLE;
--

CREATE TABLE speed (
    speedid bigint NOT NULL,
    agentid integer NOT NULL,
    taskid integer NOT NULL,
    speed bigint NOT NULL,
    "time" bigint NOT NULL
);

--
-- Name: speed_speedid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE speed_speedid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: storedvalue; Type: TABLE;
--

CREATE TABLE storedvalue (
    storedvalueid text NOT NULL,
    val text NOT NULL
);

--
-- Name: supertask; Type: TABLE;
--

CREATE TABLE supertask (
    supertaskid integer NOT NULL,
    supertaskname text NOT NULL
);

--
-- Name: supertask_supertaskid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE supertask_supertaskid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: supertaskpretask; Type: TABLE;
--

CREATE TABLE supertaskpretask (
    supertaskpretaskid integer NOT NULL,
    supertaskid integer NOT NULL,
    pretaskid integer NOT NULL
);

--
-- Name: supertaskpretask_supertaskpretaskid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE supertaskpretask_supertaskpretaskid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: task; Type: TABLE;
--

CREATE TABLE task (
    taskid integer NOT NULL,
    taskname text NOT NULL,
    attackcmd text NOT NULL,
    chunktime integer NOT NULL,
    statustimer integer NOT NULL,
    keyspace bigint NOT NULL,
    keyspaceprogress bigint NOT NULL,
    priority integer NOT NULL,
    maxagents integer NOT NULL,
    color text,
    issmall integer NOT NULL,
    iscputask integer NOT NULL,
    usenewbench integer NOT NULL,
    skipkeyspace bigint NOT NULL,
    crackerbinaryid integer,
    crackerbinarytypeid integer,
    taskwrapperid integer NOT NULL,
    isarchived integer NOT NULL,
    notes text NOT NULL,
    staticchunks integer NOT NULL,
    chunksize bigint NOT NULL,
    forcepipe integer NOT NULL,
    usepreprocessor integer NOT NULL,
    preprocessorcommand text NOT NULL
);

--
-- Name: task_taskid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE task_taskid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: taskdebugoutput; Type: TABLE;
--

CREATE TABLE taskdebugoutput (
    taskdebugoutputid integer NOT NULL,
    taskid integer NOT NULL,
    output text NOT NULL
);

--
-- Name: taskdebugoutput_taskdebugoutputid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE taskdebugoutput_taskdebugoutputid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: taskwrapper; Type: TABLE;
--

CREATE TABLE taskwrapper (
    taskwrapperid integer NOT NULL,
    priority integer NOT NULL,
    maxagents integer NOT NULL,
    tasktype integer NOT NULL,
    hashlistid integer NOT NULL,
    accessgroupid integer,
    taskwrappername text NOT NULL,
    isarchived integer NOT NULL,
    cracked integer NOT NULL
);

--
-- Name: taskwrapper_taskwrapperid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE taskwrapper_taskwrapperid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: taskwrapperdisplay; Type: VIEW;
--

CREATE VIEW taskwrapperdisplay AS
 SELECT tw.taskwrapperid,
    tw.priority AS taskwrapperpriority,
    tw.maxagents AS taskwrappermaxagents,
    tw.tasktype,
    tw.hashlistid,
    tw.accessgroupid,
    tw.taskwrappername,
    tw.isarchived AS taskwrapperisarchived,
    tw.cracked,
    t.taskid,
    t.taskname,
    t.attackcmd,
    t.chunktime,
    t.statustimer,
    t.keyspace,
    t.keyspaceprogress,
    t.priority AS taskpriority,
    t.maxagents AS taskmaxagents,
    t.isarchived AS taskisarchived,
    t.issmall,
    t.iscputask,
    t.usepreprocessor AS taskusepreprocessor,
        CASE
            WHEN (tw.tasktype = 0) THEN t.taskname
            ELSE tw.taskwrappername
        END AS displayname,
    h.hashlistname,
    h.hashcount,
    h.cracked AS hashlistcracked,
    ht.hashtypeid,
    ht.description AS hashtypedescription,
    ag.groupname,
    t.color
   FROM ((((taskwrapper tw
     LEFT JOIN task t ON (((tw.tasktype = 0) AND (t.taskwrapperid = tw.taskwrapperid))))
     JOIN hashlist h ON ((tw.hashlistid = h.hashlistid)))
     JOIN hashtype ht ON ((h.hashtypeid = ht.hashtypeid)))
     JOIN accessgroup ag ON ((tw.accessgroupid = ag.accessgroupid)));

--
-- Name: zap; Type: TABLE;
--

CREATE TABLE zap (
    zapid integer NOT NULL,
    hash text NOT NULL,
    solvetime bigint NOT NULL,
    agentid integer,
    hashlistid integer NOT NULL
);

--
-- Name: zap_zapid_seq; Type: SEQUENCE;
--

CREATE SEQUENCE zap_zapid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: accessgroup accessgroupid; Type: DEFAULT;
--

ALTER TABLE ONLY accessgroup ALTER COLUMN accessgroupid SET DEFAULT nextval('accessgroup_accessgroupid_seq'::regclass);

--
-- Name: accessgroupagent accessgroupagentid; Type: DEFAULT;
--

ALTER TABLE ONLY accessgroupagent ALTER COLUMN accessgroupagentid SET DEFAULT nextval('accessgroupagent_accessgroupagentid_seq'::regclass);

--
-- Name: accessgroupuser accessgroupuserid; Type: DEFAULT;
--

ALTER TABLE ONLY accessgroupuser ALTER COLUMN accessgroupuserid SET DEFAULT nextval('accessgroupuser_accessgroupuserid_seq'::regclass);

--
-- Name: agent agentid; Type: DEFAULT;
--

ALTER TABLE ONLY agent ALTER COLUMN agentid SET DEFAULT nextval('agent_agentid_seq'::regclass);

--
-- Name: agentbinary agentbinaryid; Type: DEFAULT;
--

ALTER TABLE ONLY agentbinary ALTER COLUMN agentbinaryid SET DEFAULT nextval('agentbinary_agentbinaryid_seq'::regclass);

--
-- Name: agenterror agenterrorid; Type: DEFAULT;
--

ALTER TABLE ONLY agenterror ALTER COLUMN agenterrorid SET DEFAULT nextval('agenterror_agenterrorid_seq'::regclass);

--
-- Name: agentstat agentstatid; Type: DEFAULT;
--

ALTER TABLE ONLY agentstat ALTER COLUMN agentstatid SET DEFAULT nextval('agentstat_agentstatid_seq'::regclass);

--
-- Name: agentzap agentzapid; Type: DEFAULT;
--

ALTER TABLE ONLY agentzap ALTER COLUMN agentzapid SET DEFAULT nextval('agentzap_agentzapid_seq'::regclass);

--
-- Name: apigroup apigroupid; Type: DEFAULT;
--

ALTER TABLE ONLY apigroup ALTER COLUMN apigroupid SET DEFAULT nextval('apigroup_apigroupid_seq'::regclass);

--
-- Name: apikey apikeyid; Type: DEFAULT;
--

ALTER TABLE ONLY apikey ALTER COLUMN apikeyid SET DEFAULT nextval('apikey_apikeyid_seq'::regclass);

--
-- Name: assignment assignmentid; Type: DEFAULT;
--

ALTER TABLE ONLY assignment ALTER COLUMN assignmentid SET DEFAULT nextval('assignment_assignmentid_seq'::regclass);

--
-- Name: chunk chunkid; Type: DEFAULT;
--

ALTER TABLE ONLY chunk ALTER COLUMN chunkid SET DEFAULT nextval('chunk_chunkid_seq'::regclass);

--
-- Name: config configid; Type: DEFAULT;
--

ALTER TABLE ONLY config ALTER COLUMN configid SET DEFAULT nextval('config_configid_seq'::regclass);

--
-- Name: configsection configsectionid; Type: DEFAULT;
--

ALTER TABLE ONLY configsection ALTER COLUMN configsectionid SET DEFAULT nextval('configsection_configsectionid_seq'::regclass);

--
-- Name: crackerbinary crackerbinaryid; Type: DEFAULT;
--

ALTER TABLE ONLY crackerbinary ALTER COLUMN crackerbinaryid SET DEFAULT nextval('crackerbinary_crackerbinaryid_seq'::regclass);

--
-- Name: crackerbinarytype crackerbinarytypeid; Type: DEFAULT;
--

ALTER TABLE ONLY crackerbinarytype ALTER COLUMN crackerbinarytypeid SET DEFAULT nextval('crackerbinarytype_crackerbinarytypeid_seq'::regclass);

--
-- Name: file fileid; Type: DEFAULT;
--

ALTER TABLE ONLY file ALTER COLUMN fileid SET DEFAULT nextval('file_fileid_seq'::regclass);

--
-- Name: filedelete filedeleteid; Type: DEFAULT;
--

ALTER TABLE ONLY filedelete ALTER COLUMN filedeleteid SET DEFAULT nextval('filedelete_filedeleteid_seq'::regclass);

--
-- Name: filedownload filedownloadid; Type: DEFAULT;
--

ALTER TABLE ONLY filedownload ALTER COLUMN filedownloadid SET DEFAULT nextval('filedownload_filedownloadid_seq'::regclass);

--
-- Name: filepretask filepretaskid; Type: DEFAULT;
--

ALTER TABLE ONLY filepretask ALTER COLUMN filepretaskid SET DEFAULT nextval('filepretask_filepretaskid_seq'::regclass);

--
-- Name: filetask filetaskid; Type: DEFAULT;
--

ALTER TABLE ONLY filetask ALTER COLUMN filetaskid SET DEFAULT nextval('filetask_filetaskid_seq'::regclass);

--
-- Name: hash hashid; Type: DEFAULT;
--

ALTER TABLE ONLY hash ALTER COLUMN hashid SET DEFAULT nextval('hash_hashid_seq'::regclass);

--
-- Name: hashbinary hashbinaryid; Type: DEFAULT;
--

ALTER TABLE ONLY hashbinary ALTER COLUMN hashbinaryid SET DEFAULT nextval('hashbinary_hashbinaryid_seq'::regclass);

--
-- Name: hashlist hashlistid; Type: DEFAULT;
--

ALTER TABLE ONLY hashlist ALTER COLUMN hashlistid SET DEFAULT nextval('hashlist_hashlistid_seq'::regclass);

--
-- Name: hashlisthashlist hashlisthashlistid; Type: DEFAULT;
--

ALTER TABLE ONLY hashlisthashlist ALTER COLUMN hashlisthashlistid SET DEFAULT nextval('hashlisthashlist_hashlisthashlistid_seq'::regclass);

--
-- Name: hashtype hashtypeid; Type: DEFAULT;
--

ALTER TABLE ONLY hashtype ALTER COLUMN hashtypeid SET DEFAULT nextval('hashtype_hashtypeid_seq'::regclass);

--
-- Name: healthcheck healthcheckid; Type: DEFAULT;
--

ALTER TABLE ONLY healthcheck ALTER COLUMN healthcheckid SET DEFAULT nextval('healthcheck_healthcheckid_seq'::regclass);

--
-- Name: healthcheckagent healthcheckagentid; Type: DEFAULT;
--

ALTER TABLE ONLY healthcheckagent ALTER COLUMN healthcheckagentid SET DEFAULT nextval('healthcheckagent_healthcheckagentid_seq'::regclass);

--
-- Name: htp_user userid; Type: DEFAULT;
--

ALTER TABLE ONLY htp_user ALTER COLUMN userid SET DEFAULT nextval('htp_user_userid_seq'::regclass);

--
-- Name: jwtapikey jwtapikeyid; Type: DEFAULT;
--

ALTER TABLE ONLY jwtapikey ALTER COLUMN jwtapikeyid SET DEFAULT nextval('jwtapikey_jwtapikeyid_seq'::regclass);

--
-- Name: logentry logentryid; Type: DEFAULT;
--

ALTER TABLE ONLY logentry ALTER COLUMN logentryid SET DEFAULT nextval('logentry_logentryid_seq'::regclass);

--
-- Name: notificationsetting notificationsettingid; Type: DEFAULT;
--

ALTER TABLE ONLY notificationsetting ALTER COLUMN notificationsettingid SET DEFAULT nextval('notificationsetting_notificationsettingid_seq'::regclass);

--
-- Name: preprocessor preprocessorid; Type: DEFAULT;
--

ALTER TABLE ONLY preprocessor ALTER COLUMN preprocessorid SET DEFAULT nextval('preprocessor_preprocessorid_seq'::regclass);

--
-- Name: pretask pretaskid; Type: DEFAULT;
--

ALTER TABLE ONLY pretask ALTER COLUMN pretaskid SET DEFAULT nextval('pretask_pretaskid_seq'::regclass);

--
-- Name: regvoucher regvoucherid; Type: DEFAULT;
--

ALTER TABLE ONLY regvoucher ALTER COLUMN regvoucherid SET DEFAULT nextval('regvoucher_regvoucherid_seq'::regclass);

--
-- Name: rightgroup rightgroupid; Type: DEFAULT;
--

ALTER TABLE ONLY rightgroup ALTER COLUMN rightgroupid SET DEFAULT nextval('rightgroup_rightgroupid_seq'::regclass);

--
-- Name: session sessionid; Type: DEFAULT;
--

ALTER TABLE ONLY session ALTER COLUMN sessionid SET DEFAULT nextval('session_sessionid_seq'::regclass);

--
-- Name: speed speedid; Type: DEFAULT;
--

ALTER TABLE ONLY speed ALTER COLUMN speedid SET DEFAULT nextval('speed_speedid_seq'::regclass);

--
-- Name: supertask supertaskid; Type: DEFAULT;
--

ALTER TABLE ONLY supertask ALTER COLUMN supertaskid SET DEFAULT nextval('supertask_supertaskid_seq'::regclass);

--
-- Name: supertaskpretask supertaskpretaskid; Type: DEFAULT;
--

ALTER TABLE ONLY supertaskpretask ALTER COLUMN supertaskpretaskid SET DEFAULT nextval('supertaskpretask_supertaskpretaskid_seq'::regclass);

--
-- Name: task taskid; Type: DEFAULT;
--

ALTER TABLE ONLY task ALTER COLUMN taskid SET DEFAULT nextval('task_taskid_seq'::regclass);

--
-- Name: taskdebugoutput taskdebugoutputid; Type: DEFAULT;
--

ALTER TABLE ONLY taskdebugoutput ALTER COLUMN taskdebugoutputid SET DEFAULT nextval('taskdebugoutput_taskdebugoutputid_seq'::regclass);

--
-- Name: taskwrapper taskwrapperid; Type: DEFAULT;
--

ALTER TABLE ONLY taskwrapper ALTER COLUMN taskwrapperid SET DEFAULT nextval('taskwrapper_taskwrapperid_seq'::regclass);

--
-- Name: zap zapid; Type: DEFAULT;
--

ALTER TABLE ONLY zap ALTER COLUMN zapid SET DEFAULT nextval('zap_zapid_seq'::regclass);

--
-- Data for Name: accessgroup; Type: TABLE DATA;
--

INSERT INTO accessgroup (accessgroupid, groupname) VALUES (1, 'Default Group');

--
-- Data for Name: agentbinary; Type: TABLE DATA;
--

INSERT INTO agentbinary (agentbinaryid, binarytype, version, operatingsystems, filename, updatetrack, updateavailable) VALUES (1, 'python', '0.7.4', 'Windows, Linux, OS X', 'hashtopolis.zip', 'stable', '');

--
-- Data for Name: apigroup; Type: TABLE DATA;
--

INSERT INTO apigroup (apigroupid, name, permissions) VALUES (1, 'Administrators', 'ALL');

--
-- Data for Name: config; Type: TABLE DATA;
--

INSERT INTO config (configid, configsectionid, item, value) VALUES (1, 1, 'agenttimeout', '30');
INSERT INTO config (configid, configsectionid, item, value) VALUES (2, 1, 'benchtime', '30');
INSERT INTO config (configid, configsectionid, item, value) VALUES (3, 1, 'chunktime', '600');
INSERT INTO config (configid, configsectionid, item, value) VALUES (4, 1, 'chunktimeout', '30');
INSERT INTO config (configid, configsectionid, item, value) VALUES (9, 1, 'fieldseparator', ':');
INSERT INTO config (configid, configsectionid, item, value) VALUES (10, 1, 'hashlistAlias', '#HL#');
INSERT INTO config (configid, configsectionid, item, value) VALUES (11, 1, 'statustimer', '5');
INSERT INTO config (configid, configsectionid, item, value) VALUES (12, 4, 'timefmt', 'd.m.Y, H:i:s');
INSERT INTO config (configid, configsectionid, item, value) VALUES (14, 3, 'numLogEntries', '5000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (15, 1, 'disptolerance', '20');
INSERT INTO config (configid, configsectionid, item, value) VALUES (16, 3, 'batchSize', '50000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (18, 2, 'yubikey_id', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (19, 2, 'yubikey_key', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (20, 2, 'yubikey_url', 'https://api.yubico.com/wsapi/2.0/verify');
INSERT INTO config (configid, configsectionid, item, value) VALUES (22, 3, 'pagingSize', '5000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (23, 3, 'plainTextMaxLength', '200');
INSERT INTO config (configid, configsectionid, item, value) VALUES (24, 3, 'hashMaxLength', '1024');
INSERT INTO config (configid, configsectionid, item, value) VALUES (25, 5, 'emailSender', 'hashtopolis@example.org');
INSERT INTO config (configid, configsectionid, item, value) VALUES (26, 5, 'emailSenderName', 'Hashtopolis');
INSERT INTO config (configid, configsectionid, item, value) VALUES (27, 5, 'baseHost', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (28, 3, 'maxHashlistSize', '5000000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (29, 4, 'hideImportMasks', '1');
INSERT INTO config (configid, configsectionid, item, value) VALUES (30, 7, 'telegramBotToken', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (31, 5, 'contactEmail', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (32, 5, 'voucherDeletion', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (33, 4, 'hashesPerPage', '1000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (34, 4, 'hideIpInfo', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (35, 1, 'defaultBenchmark', '1');
INSERT INTO config (configid, configsectionid, item, value) VALUES (36, 4, 'showTaskPerformance', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (41, 4, 'agentStatLimit', '100');
INSERT INTO config (configid, configsectionid, item, value) VALUES (42, 1, 'agentDataLifetime', '3600');
INSERT INTO config (configid, configsectionid, item, value) VALUES (43, 4, 'agentStatTension', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (44, 6, 'multicastEnable', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (45, 6, 'multicastDevice', 'eth0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (46, 6, 'multicastTransferRateEnable', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (47, 6, 'multicastTranserRate', '500000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (48, 1, 'disableTrimming', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (49, 5, 'serverLogLevel', '20');
INSERT INTO config (configid, configsectionid, item, value) VALUES (50, 7, 'notificationsProxyEnable', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (60, 7, 'notificationsProxyServer', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (61, 7, 'notificationsProxyPort', '8080');
INSERT INTO config (configid, configsectionid, item, value) VALUES (62, 7, 'notificationsProxyType', 'HTTP');
INSERT INTO config (configid, configsectionid, item, value) VALUES (63, 1, 'priority0Start', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (64, 5, 'baseUrl', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (65, 4, 'maxSessionLength', '48');
INSERT INTO config (configid, configsectionid, item, value) VALUES (66, 1, 'hashcatBrainEnable', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (67, 1, 'hashcatBrainHost', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (68, 1, 'hashcatBrainPort', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (69, 1, 'hashcatBrainPass', '');
INSERT INTO config (configid, configsectionid, item, value) VALUES (70, 1, 'hashlistImportCheck', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (71, 5, 'allowDeregister', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (72, 4, 'agentTempThreshold1', '70');
INSERT INTO config (configid, configsectionid, item, value) VALUES (73, 4, 'agentTempThreshold2', '80');
INSERT INTO config (configid, configsectionid, item, value) VALUES (74, 4, 'agentUtilThreshold1', '90');
INSERT INTO config (configid, configsectionid, item, value) VALUES (75, 4, 'agentUtilThreshold2', '75');
INSERT INTO config (configid, configsectionid, item, value) VALUES (76, 3, 'uApiSendTaskIsComplete', '0');
INSERT INTO config (configid, configsectionid, item, value) VALUES (77, 1, 'hcErrorIgnore', 'DeviceGetFanSpeed');
INSERT INTO config (configid, configsectionid, item, value) VALUES (78, 3, 'defaultPageSize', '10000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (79, 3, 'maxPageSize', '50000');
INSERT INTO config (configid, configsectionid, item, value) VALUES (13, 1, 'blacklistChars', '&|"''{}()[]$<>;`');

--
-- Data for Name: configsection; Type: TABLE DATA;
--

INSERT INTO configsection (configsectionid, sectionname) VALUES (1, 'Cracking/Tasks');
INSERT INTO configsection (configsectionid, sectionname) VALUES (2, 'Yubikey');
INSERT INTO configsection (configsectionid, sectionname) VALUES (3, 'Finetuning');
INSERT INTO configsection (configsectionid, sectionname) VALUES (4, 'UI');
INSERT INTO configsection (configsectionid, sectionname) VALUES (5, 'Server');
INSERT INTO configsection (configsectionid, sectionname) VALUES (6, 'Multicast');
INSERT INTO configsection (configsectionid, sectionname) VALUES (7, 'Notifications');

--
-- Data for Name: crackerbinary; Type: TABLE DATA;
--

INSERT INTO crackerbinary (crackerbinaryid, crackerbinarytypeid, version, downloadurl, binaryname) VALUES (1, 1, '7.1.2', 'https://hashcat.net/files/hashcat-7.1.2.7z', 'hashcat');

--
-- Data for Name: crackerbinarytype; Type: TABLE DATA;
--

INSERT INTO crackerbinarytype (crackerbinarytypeid, typename, ischunkingavailable) VALUES (1, 'hashcat', 1);

--
-- Data for Name: hashtype; Type: TABLE DATA;
--

INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (0, 'MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10, 'md5($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11, 'Joomla < 2.5.18', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12, 'PostgreSQL', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20, 'md5($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21, 'osCommerce, xt:Commerce', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22, 'Juniper Netscreen/SSG (ScreenOS)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23, 'Skype', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24, 'SolarWinds Serv-U', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30, 'md5(utf16le($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (40, 'md5($salt.utf16le($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (50, 'HMAC-MD5 (key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (60, 'HMAC-MD5 (key = $salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (70, 'md5(utf16le($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (100, 'SHA1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (101, 'nsldap, SHA-1(Base64), Netscape LDAP SHA', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (110, 'sha1($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (111, 'nsldaps, SSHA-1(Base64), Netscape LDAP SSHA', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (112, 'Oracle S: Type (Oracle 11+)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (120, 'sha1($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (121, 'SMF >= v1.1', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (122, 'OS X v10.4, v10.5, v10.6', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (124, 'Django (SHA-1)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (125, 'ArubaOS', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (130, 'sha1(utf16le($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (131, 'MSSQL(2000)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (132, 'MSSQL(2005)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (133, 'PeopleSoft', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (140, 'sha1($salt.utf16le($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (141, 'EPiServer 6.x < v4', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (150, 'HMAC-SHA1 (key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (160, 'HMAC-SHA1 (key = $salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (170, 'sha1(utf16le($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (200, 'MySQL323', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (300, 'MySQL4.1/MySQL5+', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (400, 'phpass, MD5(Wordpress), MD5(Joomla), MD5(phpBB3)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (500, 'md5crypt, MD5(Unix), FreeBSD MD5, Cisco-IOS MD5 2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (501, 'Juniper IVE', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (600, 'BLAKE2b-512', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (610, 'BLAKE2b-512($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (620, 'BLAKE2b-512($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (900, 'MD4', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1000, 'NTLM', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1100, 'Domain Cached Credentials (DCC), MS Cache', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1300, 'SHA-224', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1310, 'sha224($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1320, 'sha224($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1400, 'SHA256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1410, 'sha256($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1411, 'SSHA-256(Base64), LDAP {SSHA256}', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1420, 'sha256($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1421, 'hMailServer', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1430, 'sha256(utf16le($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1440, 'sha256($salt.utf16le($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1441, 'EPiServer 6.x >= v4', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1450, 'HMAC-SHA256 (key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1460, 'HMAC-SHA256 (key = $salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1470, 'sha256(utf16le($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1500, 'descrypt, DES(Unix), Traditional DES', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1600, 'md5apr1, MD5(APR), Apache MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1700, 'SHA512', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1710, 'sha512($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1711, 'SSHA-512(Base64), LDAP {SSHA512}', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1720, 'sha512($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1722, 'OS X v10.7', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1730, 'sha512(utf16le($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1731, 'MSSQL(2012), MSSQL(2014)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1740, 'sha512($salt.utf16le($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1750, 'HMAC-SHA512 (key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1760, 'HMAC-SHA512 (key = $salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1770, 'sha512(utf16le($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (1800, 'sha512crypt, SHA512(Unix)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2000, 'STDOUT', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2100, 'Domain Cached Credentials 2 (DCC2), MS Cache', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2400, 'Cisco-PIX MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2410, 'Cisco-ASA MD5', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2500, 'WPA/WPA2', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2501, 'WPA-EAPOL-PMK', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2600, 'md5(md5($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2611, 'vBulletin < v3.8.5', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2612, 'PHPS', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2630, 'md5(md5($pass.$salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2711, 'vBulletin >= v3.8.5', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (2811, 'IPB2+, MyBB1.2+', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3000, 'LM', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3100, 'Oracle H: Type (Oracle 7+), DES(Oracle)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3200, 'bcrypt, Blowfish(OpenBSD)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3500, 'md5(md5(md5($pass)))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3610, 'md5(md5(md5($pass)).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3710, 'md5($salt.md5($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3711, 'Mediawiki B type', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3730, 'md5($salt1.strtoupper(md5($salt2.$pass)))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3800, 'md5($salt.$pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (3910, 'md5(md5($pass).md5($salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4010, 'md5($salt.md5($salt.$pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4110, 'md5($salt.md5($pass.$salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4300, 'md5(strtoupper(md5($pass)))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4400, 'md5(sha1($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4410, 'md5(sha1($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4420, 'md5(sha1($pass.$salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4430, 'md5(sha1($salt.$pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4500, 'sha1(sha1($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4510, 'sha1(sha1($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4520, 'sha1($salt.sha1($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4521, 'Redmine Project Management Web App', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4522, 'PunBB', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4700, 'sha1(md5($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4710, 'sha1(md5($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4711, 'Huawei sha1(md5($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4800, 'MD5(Chap), iSCSI CHAP authentication', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (4900, 'sha1($salt.$pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5000, 'SHA-3(Keccak)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5100, 'Half MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5200, 'Password Safe v3', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5300, 'IKE-PSK MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5400, 'IKE-PSK SHA1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5500, 'NetNTLMv1-VANILLA / NetNTLMv1+ESS', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5600, 'NetNTLMv2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5700, 'Cisco-IOS SHA256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5720, 'Cisco-ISE Hashed Password (SHA256)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (5800, 'Samsung Android Password/PIN', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6000, 'RipeMD160', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6050, 'HMAC-RIPEMD160 (key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6060, 'HMAC-RIPEMD160 (key = $salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6100, 'Whirlpool', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6211, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6212, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6213, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6221, 'TrueCrypt 5.0+ SHA512 + AES/Serpent/Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6222, 'TrueCrypt 5.0+ SHA512 + AES-Twofish/Serpent-AES/Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6223, 'TrueCrypt 5.0+ SHA512 + AES-Twofish-Serpent/Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6231, 'TrueCrypt 5.0+ Whirlpool + AES/Serpent/Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6232, 'TrueCrypt 5.0+ Whirlpool + AES-Twofish/Serpent-AES/Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6233, 'TrueCrypt 5.0+ Whirlpool + AES-Twofish-Serpent/Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6241, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES/Serpent/Twofish + boot', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6242, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish/Serpent-AES/Twofish-Serpent + boot', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6243, 'TrueCrypt 5.0+ PBKDF2-HMAC-RipeMD160 + AES-Twofish-Serpent/Serpent-Twofish-AES + boot', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6300, 'AIX {smd5}', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6400, 'AIX {ssha256}', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6500, 'AIX {ssha512}', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6600, '1Password, Agile Keychain', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6700, 'AIX {ssha1}', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6800, 'Lastpass', 1, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (6900, 'GOST R 34.11-94', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7000, 'Fortigate (FortiOS)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7100, 'OS X v10.8 / v10.9', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7200, 'GRUB 2', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7300, 'IPMI2 RAKP HMAC-SHA1', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7350, 'IPMI2 RAKP HMAC-MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7400, 'sha256crypt, SHA256(Unix)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7401, 'MySQL $A$ (sha256crypt)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7500, 'Kerberos 5 AS-REQ Pre-Auth', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7700, 'SAP CODVN B (BCODE)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7701, 'SAP CODVN B (BCODE) from RFC_READ_TABLE', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7800, 'SAP CODVN F/G (PASSCODE)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7801, 'SAP CODVN F/G (PASSCODE) from RFC_READ_TABLE', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (7900, 'Drupal7', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8000, 'Sybase ASE', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8100, 'Citrix Netscaler', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8200, '1Password, Cloud Keychain', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8300, 'DNSSEC (NSEC3)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8400, 'WBB3, Woltlab Burning Board 3', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8500, 'RACF', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8501, 'AS/400 DES', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8600, 'Lotus Notes/Domino 5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8700, 'Lotus Notes/Domino 6', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8800, 'Android FDE <= 4.3', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (8900, 'scrypt', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9000, 'Password Safe v2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9100, 'Lotus Notes/Domino', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9200, 'Cisco $8$', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9300, 'Cisco $9$', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9400, 'Office 2007', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9500, 'Office 2010', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9600, 'Office 2013', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9700, 'MS Office ⇐ 2003 MD5 + RC4, oldoffice$0, oldoffice$1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9710, 'MS Office <= 2003 $0/$1, MD5 + RC4, collider #1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9720, 'MS Office <= 2003 $0/$1, MD5 + RC4, collider #2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9800, 'MS Office ⇐ 2003 SHA1 + RC4, oldoffice$3, oldoffice$4', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9810, 'MS Office <= 2003 $3, SHA1 + RC4, collider #1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9820, 'MS Office <= 2003 $3, SHA1 + RC4, collider #2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (9900, 'Radmin2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10000, 'Django (PBKDF2-SHA256)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10100, 'SipHash', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10200, 'Cram MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10300, 'SAP CODVN H (PWDSALTEDHASH) iSSHA-1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10400, 'PDF 1.1 - 1.3 (Acrobat 2 - 4)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10410, 'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10420, 'PDF 1.1 - 1.3 (Acrobat 2 - 4), collider #2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10500, 'PDF 1.4 - 1.6 (Acrobat 5 - 8)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10510, 'PDF 1.3 - 1.6 (Acrobat 4 - 8) w/ RC4-40', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10600, 'PDF 1.7 Level 3 (Acrobat 9)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10700, 'PDF 1.7 Level 8 (Acrobat 10 - 11)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10800, 'SHA384', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10810, 'sha384($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10820, 'sha384($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10830, 'sha384(utf16le($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10840, 'sha384($salt.utf16le($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10870, 'sha384(utf16le($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10900, 'PBKDF2-HMAC-SHA256', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (10901, 'RedHat 389-DS LDAP (PBKDF2-HMAC-SHA256)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11000, 'PrestaShop', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11100, 'PostgreSQL Challenge-Response Authentication (MD5)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11200, 'MySQL Challenge-Response Authentication (SHA1)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11300, 'Bitcoin/Litecoin wallet.dat', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11400, 'SIP digest authentication (MD5)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11500, 'CRC32', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11600, '7-Zip', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11700, 'GOST R 34.11-2012 (Streebog) 256-bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11750, 'HMAC-Streebog-256 (key = $pass), big-endian', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11760, 'HMAC-Streebog-256 (key = $salt), big-endian', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11800, 'GOST R 34.11-2012 (Streebog) 512-bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11850, 'HMAC-Streebog-512 (key = $pass), big-endian', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11860, 'HMAC-Streebog-512 (key = $salt), big-endian', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (11900, 'PBKDF2-HMAC-MD5', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12000, 'PBKDF2-HMAC-SHA1', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12001, 'Atlassian (PBKDF2-HMAC-SHA1)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12100, 'PBKDF2-HMAC-SHA512', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12150, 'Apache Shiro 1 SHA-512', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12200, 'eCryptfs', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12300, 'Oracle T: Type (Oracle 12+)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12400, 'BSDiCrypt, Extended DES', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12500, 'RAR3-hp', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12600, 'ColdFusion 10+', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12700, 'Blockchain, My Wallet', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12800, 'MS-AzureSync PBKDF2-HMAC-SHA256', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (12900, 'Android FDE (Samsung DEK)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13000, 'RAR5', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13100, 'Kerberos 5 TGS-REP etype 23', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13200, 'AxCrypt', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13300, 'AxCrypt in memory SHA1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13400, 'Keepass 1/2 AES/Twofish with/without keyfile', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13500, 'PeopleSoft PS_TOKEN', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13600, 'WinZip', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13711, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES, Serpent, Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13712, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13713, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20730, 'sha256(sha256($pass.$salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13721, 'VeraCrypt PBKDF2-HMAC-SHA512 + AES, Serpent, Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13722, 'VeraCrypt PBKDF2-HMAC-SHA512 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13723, 'VeraCrypt PBKDF2-HMAC-SHA512 + Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13731, 'VeraCrypt PBKDF2-HMAC-Whirlpool + AES, Serpent, Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13732, 'VeraCrypt PBKDF2-HMAC-Whirlpool + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13733, 'VeraCrypt PBKDF2-HMAC-Whirlpool + Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13741, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13742, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13743, 'VeraCrypt PBKDF2-HMAC-RIPEMD160 + boot-mode + AES-Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13751, 'VeraCrypt PBKDF2-HMAC-SHA256 + AES, Serpent, Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13752, 'VeraCrypt PBKDF2-HMAC-SHA256 + AES-Twofish, Serpent-AES, Twofish-Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13753, 'VeraCrypt PBKDF2-HMAC-SHA256 + Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13761, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode (PIM + AES | Twofish)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13762, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13763, 'VeraCrypt PBKDF2-HMAC-SHA256 + boot-mode + Serpent-Twofish-AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13771, 'VeraCrypt Streebog-512 + XTS 512 bit', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13772, 'VeraCrypt Streebog-512 + XTS 1024 bit', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13773, 'VeraCrypt Streebog-512 + XTS 1536 bit', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13781, 'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode (legacy)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13782, 'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode (legacy)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13783, 'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode (legacy)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13800, 'Windows 8+ phone PIN/Password', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (13900, 'OpenCart', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14000, 'DES (PT = $salt, key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14100, '3DES (PT = $salt, key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14200, 'RACF KDFAES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14400, 'sha1(CX)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14500, 'Linux Kernel Crypto API (2.4)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14600, 'LUKS 10', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14700, 'iTunes Backup < 10.0 11', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14800, 'iTunes Backup >= 10.0 11', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (14900, 'Skip32 12', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15000, 'FileZilla Server >= 0.9.55', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15100, 'Juniper/NetBSD sha1crypt', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15200, 'Blockchain, My Wallet, V2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15300, 'DPAPI masterkey file v1 and v2', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15310, 'DPAPI masterkey file v1 (context 3)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15400, 'ChaCha20', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15500, 'JKS Java Key Store Private Keys (SHA1)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15600, 'Ethereum Wallet, PBKDF2-HMAC-SHA256', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15700, 'Ethereum Wallet, SCRYPT', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15900, 'DPAPI master key file version 2 + Active Directory domain context', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (15910, 'DPAPI masterkey file v2 (context 3)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16000, 'Tripcode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16100, 'TACACS+', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16200, 'Apple Secure Notes', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16300, 'Ethereum Pre-Sale Wallet, PBKDF2-HMAC-SHA256', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16400, 'CRAM-MD5 Dovecot', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16500, 'JWT (JSON Web Token)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16501, 'Perl Mojolicious session cookie (HMAC-SHA256, >= v9.19)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16600, 'Electrum Wallet (Salt-Type 1-3)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16700, 'FileVault 2', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16800, 'WPA-PMKID-PBKDF2', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16801, 'WPA-PMKID-PMK', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (16900, 'Ansible Vault', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17010, 'GPG (AES-128/AES-256 (SHA-1($pass)))', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17020, 'GPG (AES-128/AES-256 (SHA-512($pass)))', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17030, 'GPG (AES-128/AES-256 (SHA-256($pass)))', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17040, 'GPG (CAST5 (SHA-1($pass)))', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17200, 'PKZIP (Compressed)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17210, 'PKZIP (Uncompressed)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17220, 'PKZIP (Compressed Multi-File)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17225, 'PKZIP (Mixed Multi-File)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17230, 'PKZIP (Compressed Multi-File Checksum-Only)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17300, 'SHA3-224', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17400, 'SHA3-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17500, 'SHA3-384', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17600, 'SHA3-512', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17700, 'Keccak-224', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17800, 'Keccak-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (17900, 'Keccak-384', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18000, 'Keccak-512', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18100, 'TOTP (HMAC-SHA1)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18200, 'Kerberos 5 AS-REP etype 23', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18300, 'Apple File System (APFS)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18400, 'Open Document Format (ODF) 1.2 (SHA-256, AES)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18500, 'sha1(md5(md5($pass)))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18600, 'Open Document Format (ODF) 1.1 (SHA-1, Blowfish)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18700, 'Java Object hashCode()', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18800, 'Blockchain, My Wallet, Second Password (SHA256)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (18900, 'Android Backup', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19000, 'QNX /etc/shadow (MD5)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19100, 'QNX /etc/shadow (SHA256)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19200, 'QNX /etc/shadow (SHA512)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19210, 'QNX 7 /etc/shadow (SHA512)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19300, 'sha1($salt1.$pass.$salt2)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19500, 'Ruby on Rails Restful-Authentication', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19600, 'Kerberos 5 TGS-REP etype 17 (AES128-CTS-HMAC-SHA1-96)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19700, 'Kerberos 5 TGS-REP etype 18 (AES256-CTS-HMAC-SHA1-96)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19800, 'Kerberos 5, etype 17, Pre-Auth', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (19900, 'Kerberos 5, etype 18, Pre-Auth', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20011, 'DiskCryptor SHA512 + XTS 512 bit (AES) / DiskCryptor SHA512 + XTS 512 bit (Twofish) / DiskCryptor SHA512 + XTS 512 bit (Serpent)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20012, 'DiskCryptor SHA512 + XTS 1024 bit (AES-Twofish) / DiskCryptor SHA512 + XTS 1024 bit (Twofish-Serpent) / DiskCryptor SHA512 + XTS 1024 bit (Serpent-AES)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20013, 'DiskCryptor SHA512 + XTS 1536 bit (AES-Twofish-Serpent)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20200, 'Python passlib pbkdf2-sha512', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20300, 'Python passlib pbkdf2-sha256', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20400, 'Python passlib pbkdf2-sha1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20500, 'PKZIP Master Key', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20510, 'PKZIP Master Key (6 byte optimization)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20600, 'Oracle Transportation Management (SHA256)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20710, 'sha256(sha256($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20711, 'AuthMe sha256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20712, 'RSA Security Analytics / NetWitness (sha256)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20720, 'sha256($salt.sha256($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20800, 'sha256(md5($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (20900, 'md5(sha1($pass).md5($pass).sha1($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21000, 'BitShares v0.x - sha512(sha512_bin(pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21100, 'sha1(md5($pass.$salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21200, 'md5(sha1($salt).md5($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21300, 'md5($salt.sha1($salt.$pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21310, 'md5($salt1.sha1($salt2.$pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21400, 'sha256(sha256_bin(pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21420, 'sha256($salt.sha256_bin($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21500, 'SolarWinds Orion', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21501, 'SolarWinds Orion v2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21600, 'Web2py pbkdf2-sha512', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21700, 'Electrum Wallet (Salt-Type 4)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21800, 'Electrum Wallet (Salt-Type 5)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (21900, 'md5(md5(md5($pass.$salt1)).$salt2)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22000, 'WPA-PBKDF2-PMKID+EAPOL', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22001, 'WPA-PMK-PMKID+EAPOL', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22100, 'BitLocker', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22200, 'Citrix NetScaler (SHA512)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22300, 'sha256($salt.$pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22301, 'Telegram client app passcode (SHA256)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22400, 'AES Crypt (SHA256)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22500, 'MultiBit Classic .key (MD5)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22600, 'Telegram Desktop App Passcode (PBKDF2-HMAC-SHA1)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22700, 'MultiBit HD (scrypt)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22800, 'Simpla CMS - md5($salt.$pass.md5($pass))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22911, 'RSA/DSA/EC/OPENSSH Private Keys ($0$)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22921, 'RSA/DSA/EC/OPENSSH Private Keys ($6$)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22931, 'RSA/DSA/EC/OPENSSH Private Keys ($1, $3$)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22941, 'RSA/DSA/EC/OPENSSH Private Keys ($4$)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (22951, 'RSA/DSA/EC/OPENSSH Private Keys ($5$)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23001, 'SecureZIP AES-128', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23002, 'SecureZIP AES-192', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23003, 'SecureZIP AES-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23100, 'Apple Keychain', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23200, 'XMPP SCRAM PBKDF2-SHA1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23300, 'Apple iWork', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23400, 'Bitwarden', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23500, 'AxCrypt 2 AES-128', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23600, 'AxCrypt 2 AES-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23700, 'RAR3-p (Uncompressed)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23800, 'RAR3-p (Compressed)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (23900, 'BestCrypt v3 Volume Encryption', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24000, 'BestCrypt v4 Volume Encryption', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24100, 'MongoDB ServerKey SCRAM-SHA-1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24200, 'MongoDB ServerKey SCRAM-SHA-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24300, 'sha1($salt.sha1($pass.$salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24410, 'PKCS#8 Private Keys (PBKDF2-HMAC-SHA1 + 3DES/AES)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24420, 'PKCS#8 Private Keys (PBKDF2-HMAC-SHA256 + 3DES/AES)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24500, 'Telegram Desktop >= v2.1.14 (PBKDF2-HMAC-SHA512)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24600, 'SQLCipher', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24700, 'Stuffit5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24800, 'Umbraco HMAC-SHA1', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (24900, 'Dahua Authentication MD5', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25000, 'SNMPv3 HMAC-MD5-96/HMAC-SHA1-96', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25100, 'SNMPv3 HMAC-MD5-96', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25200, 'SNMPv3 HMAC-SHA1-96', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25300, 'MS Office 2016 - SheetProtection', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25400, 'PDF 1.4 - 1.6 (Acrobat 5 - 8) - edit password', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25500, 'Stargazer Stellar Wallet XLM', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25600, 'bcrypt(md5($pass)) / bcryptmd5', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25700, 'MurmurHash', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25800, 'bcrypt(sha1($pass)) / bcryptsha1', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (25900, 'KNX IP Secure - Device Authentication Code', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26000, 'Mozilla key3.db', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26100, 'Mozilla key4.db', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26200, 'OpenEdge Progress Encode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26300, 'FortiGate256 (FortiOS256)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26401, 'AES-128-ECB NOKDF (PT = $salt, key = $pass)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26402, 'AES-192-ECB NOKDF (PT = $salt, key = $pass)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26403, 'AES-256-ECB NOKDF (PT = $salt, key = $pass)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26500, 'iPhone passcode (UID key + System Keybag)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26600, 'MetaMask Wallet', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26610, 'MetaMask Wallet (short hash, plaintext check)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26700, 'SNMPv3 HMAC-SHA224-128', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26800, 'SNMPv3 HMAC-SHA256-192', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (26900, 'SNMPv3 HMAC-SHA384-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27000, 'NetNTLMv1 / NetNTLMv1+ESS (NT)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27100, 'NetNTLMv2 (NT)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27200, 'Ruby on Rails Restful Auth (one round, no sitekey)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27300, 'SNMPv3 HMAC-SHA512-384', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27400, 'VMware VMX (PBKDF2-HMAC-SHA1 + AES-256-CBC)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27500, 'VirtualBox (PBKDF2-HMAC-SHA256 & AES-128-XTS)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27600, 'VirtualBox (PBKDF2-HMAC-SHA256 & AES-256-XTS)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27700, 'MultiBit Classic .wallet (scrypt)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27800, 'MurmurHash3', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (27900, 'CRC32C', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28000, 'CRC64Jones', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28100, 'Windows Hello PIN/Password', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28200, 'Exodus Desktop Wallet (scrypt)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28300, 'Teamspeak 3 (channel hash)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28400, 'bcrypt(sha512($pass)) / bcryptsha512', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28501, 'Bitcoin WIF private key (P2PKH), compressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28502, 'Bitcoin WIF private key (P2PKH), uncompressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28503, 'Bitcoin WIF private key (P2WPKH, Bech32), compressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28504, 'Bitcoin WIF private key (P2WPKH, Bech32), uncompressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28505, 'Bitcoin WIF private key (P2SH(P2WPKH)), compressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28506, 'Bitcoin WIF private key (P2SH(P2WPKH)), uncompressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28600, 'PostgreSQL SCRAM-SHA-256', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28700, 'Amazon AWS4-HMAC-SHA256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28800, 'Kerberos 5, etype 17, DB', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (28900, 'Kerberos 5, etype 18, DB', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29000, 'sha1($salt.sha1(utf16le($username).'':''.utf16le($pass)))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29100, 'Flask Session Cookie ($salt.$salt.$pass)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29200, 'Radmin3', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29311, 'TrueCrypt RIPEMD160 + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29312, 'TrueCrypt RIPEMD160 + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29313, 'TrueCrypt RIPEMD160 + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29321, 'TrueCrypt SHA512 + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29322, 'TrueCrypt SHA512 + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29323, 'TrueCrypt SHA512 + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29331, 'TrueCrypt Whirlpool + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29332, 'TrueCrypt Whirlpool + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29333, 'TrueCrypt Whirlpool + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29341, 'TrueCrypt RIPEMD160 + XTS 512 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29342, 'TrueCrypt RIPEMD160 + XTS 1024 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29343, 'TrueCrypt RIPEMD160 + XTS 1536 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29411, 'VeraCrypt RIPEMD160 + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29412, 'VeraCrypt RIPEMD160 + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29413, 'VeraCrypt RIPEMD160 + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29421, 'VeraCrypt SHA512 + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29422, 'VeraCrypt SHA512 + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29423, 'VeraCrypt SHA512 + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29431, 'VeraCrypt Whirlpool + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29432, 'VeraCrypt Whirlpool + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29433, 'VeraCrypt Whirlpool + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29441, 'VeraCrypt RIPEMD160 + XTS 512 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29442, 'VeraCrypt RIPEMD160 + XTS 1024 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29443, 'VeraCrypt RIPEMD160 + XTS 1536 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29451, 'VeraCrypt SHA256 + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29452, 'VeraCrypt SHA256 + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29453, 'VeraCrypt SHA256 + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29461, 'VeraCrypt SHA256 + XTS 512 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29462, 'VeraCrypt SHA256 + XTS 1024 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29463, 'VeraCrypt SHA256 + XTS 1536 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29471, 'VeraCrypt Streebog-512 + XTS 512 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29472, 'VeraCrypt Streebog-512 + XTS 1024 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29473, 'VeraCrypt Streebog-512 + XTS 1536 bit', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29481, 'VeraCrypt Streebog-512 + XTS 512 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29482, 'VeraCrypt Streebog-512 + XTS 1024 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29483, 'VeraCrypt Streebog-512 + XTS 1536 bit + boot-mode', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29511, 'LUKS v1 SHA-1 + AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29512, 'LUKS v1 SHA-1 + Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29513, 'LUKS v1 SHA-1 + Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29521, 'LUKS v1 SHA-256 + AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29522, 'LUKS v1 SHA-256 + Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29523, 'LUKS v1 SHA-256 + Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29531, 'LUKS v1 SHA-512 + AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29532, 'LUKS v1 SHA-512 + Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29533, 'LUKS v1 SHA-512 + Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29541, 'LUKS v1 RIPEMD-160 + AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29542, 'LUKS v1 RIPEMD-160 + Serpent', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29543, 'LUKS v1 RIPEMD-160 + Twofish', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29600, 'Terra Station Wallet (AES256-CBC(PBKDF2($pass)))', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29700, 'KeePass 1 (AES/Twofish) and KeePass 2 (AES) - keyfile only mode', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29800, 'Bisq .wallet (scrypt)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29910, 'ENCsecurity Datavault (PBKDF2/no keychain)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29920, 'ENCsecurity Datavault (PBKDF2/keychain)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29930, 'ENCsecurity Datavault (MD5/no keychain)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (29940, 'ENCsecurity Datavault (MD5/keychain)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30000, 'Python Werkzeug MD5 (HMAC-MD5 (key = $salt))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30120, 'Python Werkzeug SHA256 (HMAC-SHA256 (key = $salt))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30420, 'DANE RFC7929/RFC8162 SHA2-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30500, 'md5(md5($salt).md5(md5($pass)))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30600, 'bcrypt(sha256($pass))', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30601, 'bcrypt(HMAC-SHA256($pass))', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30700, 'Anope IRC Services (enc_sha256)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30901, 'Bitcoin raw private key (P2PKH), compressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30902, 'Bitcoin raw private key (P2PKH), uncompressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30903, 'Bitcoin raw private key (P2WPKH, Bech32), compressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30904, 'Bitcoin raw private key (P2WPKH, Bech32), uncompressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30905, 'Bitcoin raw private key (P2SH(P2WPKH)), compressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (30906, 'Bitcoin raw private key (P2SH(P2WPKH)), uncompressed', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31000, 'BLAKE2s-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31100, 'ShangMi 3 (SM3)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31200, 'Veeam VBK', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31300, 'MS SNTP', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31400, 'SecureCRT MasterPassphrase v2', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31500, 'Domain Cached Credentials (DCC), MS Cache (NT)', 1, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31600, 'Domain Cached Credentials 2 (DCC2), MS Cache 2, (NT)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31700, 'md5(md5(md5($pass).$salt1).$salt2)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31800, '1Password, mobilekeychain (1Password 8)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (31900, 'MetaMask Mobile Wallet', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32000, 'NetIQ SSPR (MD5)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32010, 'NetIQ SSPR (SHA1)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32020, 'NetIQ SSPR (SHA-1 with Salt)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32030, 'NetIQ SSPR (SHA-256 with Salt)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32031, 'Adobe AEM (SSPR, SHA-256 with Salt)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32040, 'NetIQ SSPR (SHA-512 with Salt)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32041, 'Adobe AEM (SSPR, SHA-512 with Salt)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32050, 'NetIQ SSPR (PBKDF2WithHmacSHA1)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32060, 'NetIQ SSPR (PBKDF2WithHmacSHA256)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32070, 'NetIQ SSPR (PBKDF2WithHmacSHA512)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32100, 'Kerberos 5, etype 17, AS-REP', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32200, 'Kerberos 5, etype 18, AS-REP', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32300, 'Empire CMS (Admin password)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32410, 'sha512(sha512($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32420, 'sha512(sha512_bin($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32500, 'Dogechain.info Wallet', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32600, 'CubeCart (whirlpool($salt.$pass.$salt))', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32700, 'Kremlin Encrypt 3.0 w/NewDES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32800, 'md5(sha1(md5($pass)))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (32900, 'PBKDF1-SHA1', 1, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33000, 'md5($salt1.$pass.$salt2)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33100, 'md5($salt.md5($pass).$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33300, 'HMAC-BLAKE2S (key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33400, 'mega.nz password-protected link (PBKDF2-HMAC-SHA512)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33500, 'RC4 40-bit DropN', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33501, 'RC4 72-bit DropN', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33502, 'RC4 104-bit DropN', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33600, 'RIPEMD-320', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33650, 'HMAC-RIPEMD320 (key = $pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33660, 'HMAC-RIPEMD320 (key = $salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33700, 'Microsoft Online Account (PBKDF2-HMAC-SHA256 + AES256)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33800, 'WBB4 (Woltlab Burning Board) [bcrypt(bcrypt($pass))]', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (33900, 'Citrix NetScaler (PBKDF2-HMAC-SHA256)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34000, 'Argon2', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34100, 'LUKS v2 argon2 + SHA-256 + AES', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34200, 'MurmurHash64A', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34201, 'MurmurHash64A (zero seed)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34211, 'MurmurHash64A truncated (zero seed)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34300, 'KeePass (KDBX v4)', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34400, 'sha224(sha224($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34500, 'sha224(sha1($pass))', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34600, 'MD6 (256)', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34700, 'Blockchain, My Wallet, Legacy Wallets', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34800, 'BLAKE2b-256', 0, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34810, 'BLAKE2b-256($pass.$salt)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (34820, 'BLAKE2b-256($salt.$pass)', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (35000, 'SAP CODVN H (PWDSALTEDHASH) isSHA512', 1, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (35100, 'sm3crypt $sm3$, SM3 (Unix)', 1, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (35200, 'AS/400 SSHA1', 1, 0);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (70000, 'Argon2id [Bridged: reference implementation + tunings]', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (70100, 'scrypt [Bridged: Scrypt-Jane SMix]', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (70200, 'scrypt [Bridged: Scrypt-Yescrypt]', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (72000, 'Generic Hash [Bridged: Python Interpreter free-threading]', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (73000, 'Generic Hash [Bridged: Python Interpreter with GIL]', 0, 1);
INSERT INTO hashtype (hashtypeid, description, issalted, isslowhash) VALUES (99999, 'Plaintext', 0, 0);

--
-- Data for Name: preprocessor; Type: TABLE DATA;
--

INSERT INTO preprocessor (preprocessorid, name, url, binaryname, keyspacecommand, skipcommand, limitcommand) VALUES (1, 'Prince', 'https://github.com/hashcat/princeprocessor/releases/download/v0.22/princeprocessor-0.22.7z', 'pp', '--keyspace', '--skip', '--limit');

--
-- Data for Name: rightgroup; Type: TABLE DATA;
--

INSERT INTO rightgroup (rightgroupid, groupname, permissions) VALUES (1, 'Administrator', 'ALL');

--
-- Name: accessgroup_accessgroupid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('accessgroup_accessgroupid_seq', 1, true);

--
-- Name: accessgroupagent_accessgroupagentid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('accessgroupagent_accessgroupagentid_seq', 1, false);

--
-- Name: accessgroupuser_accessgroupuserid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('accessgroupuser_accessgroupuserid_seq', 1, false);

--
-- Name: agent_agentid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('agent_agentid_seq', 1, false);

--
-- Name: agentbinary_agentbinaryid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('agentbinary_agentbinaryid_seq', 1, true);

--
-- Name: agenterror_agenterrorid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('agenterror_agenterrorid_seq', 1, false);

--
-- Name: agentstat_agentstatid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('agentstat_agentstatid_seq', 1, false);

--
-- Name: agentzap_agentzapid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('agentzap_agentzapid_seq', 1, false);

--
-- Name: apigroup_apigroupid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('apigroup_apigroupid_seq', 1, true);

--
-- Name: apikey_apikeyid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('apikey_apikeyid_seq', 1, false);

--
-- Name: assignment_assignmentid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('assignment_assignmentid_seq', 1, false);

--
-- Name: chunk_chunkid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('chunk_chunkid_seq', 1, false);

--
-- Name: config_configid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('config_configid_seq', 79, true);

--
-- Name: configsection_configsectionid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('configsection_configsectionid_seq', 7, true);

--
-- Name: crackerbinary_crackerbinaryid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('crackerbinary_crackerbinaryid_seq', 1, true);

--
-- Name: crackerbinarytype_crackerbinarytypeid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('crackerbinarytype_crackerbinarytypeid_seq', 1, true);

--
-- Name: file_fileid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('file_fileid_seq', 1, false);

--
-- Name: filedelete_filedeleteid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('filedelete_filedeleteid_seq', 1, false);

--
-- Name: filedownload_filedownloadid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('filedownload_filedownloadid_seq', 1, false);

--
-- Name: filepretask_filepretaskid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('filepretask_filepretaskid_seq', 1, false);

--
-- Name: filetask_filetaskid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('filetask_filetaskid_seq', 1, false);

--
-- Name: hash_hashid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('hash_hashid_seq', 1, false);

--
-- Name: hashbinary_hashbinaryid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('hashbinary_hashbinaryid_seq', 1, false);

--
-- Name: hashlist_hashlistid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('hashlist_hashlistid_seq', 1, false);

--
-- Name: hashlisthashlist_hashlisthashlistid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('hashlisthashlist_hashlisthashlistid_seq', 1, false);

--
-- Name: hashtype_hashtypeid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('hashtype_hashtypeid_seq', 99999, true);

--
-- Name: healthcheck_healthcheckid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('healthcheck_healthcheckid_seq', 1, false);

--
-- Name: healthcheckagent_healthcheckagentid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('healthcheckagent_healthcheckagentid_seq', 1, false);

--
-- Name: htp_user_userid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('htp_user_userid_seq', 1, false);

--
-- Name: jwtapikey_jwtapikeyid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('jwtapikey_jwtapikeyid_seq', 1, false);

--
-- Name: logentry_logentryid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('logentry_logentryid_seq', 1, false);

--
-- Name: notificationsetting_notificationsettingid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('notificationsetting_notificationsettingid_seq', 1, false);

--
-- Name: preprocessor_preprocessorid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('preprocessor_preprocessorid_seq', 1, true);

--
-- Name: pretask_pretaskid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('pretask_pretaskid_seq', 1, false);

--
-- Name: regvoucher_regvoucherid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('regvoucher_regvoucherid_seq', 1, false);

--
-- Name: rightgroup_rightgroupid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('rightgroup_rightgroupid_seq', 1, true);

--
-- Name: session_sessionid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('session_sessionid_seq', 1, false);

--
-- Name: speed_speedid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('speed_speedid_seq', 1, false);

--
-- Name: supertask_supertaskid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('supertask_supertaskid_seq', 1, false);

--
-- Name: supertaskpretask_supertaskpretaskid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('supertaskpretask_supertaskpretaskid_seq', 1, false);

--
-- Name: task_taskid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('task_taskid_seq', 1, false);

--
-- Name: taskdebugoutput_taskdebugoutputid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('taskdebugoutput_taskdebugoutputid_seq', 1, false);

--
-- Name: taskwrapper_taskwrapperid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('taskwrapper_taskwrapperid_seq', 1, false);

--
-- Name: zap_zapid_seq; Type: SEQUENCE SET;
--

SELECT pg_catalog.setval('zap_zapid_seq', 1, false);

--
-- Name: accessgroup accessgroup_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY accessgroup
    ADD CONSTRAINT accessgroup_pkey PRIMARY KEY (accessgroupid);

--
-- Name: accessgroupagent accessgroupagent_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY accessgroupagent
    ADD CONSTRAINT accessgroupagent_pkey PRIMARY KEY (accessgroupagentid);

--
-- Name: accessgroupuser accessgroupuser_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY accessgroupuser
    ADD CONSTRAINT accessgroupuser_pkey PRIMARY KEY (accessgroupuserid);

--
-- Name: agent agent_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT agent_pkey PRIMARY KEY (agentid);

--
-- Name: agentbinary agentbinary_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY agentbinary
    ADD CONSTRAINT agentbinary_pkey PRIMARY KEY (agentbinaryid);

--
-- Name: agenterror agenterror_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY agenterror
    ADD CONSTRAINT agenterror_pkey PRIMARY KEY (agenterrorid);

--
-- Name: agentstat agentstat_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY agentstat
    ADD CONSTRAINT agentstat_pkey PRIMARY KEY (agentstatid);

--
-- Name: agentzap agentzap_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY agentzap
    ADD CONSTRAINT agentzap_pkey PRIMARY KEY (agentzapid);

--
-- Name: apigroup apigroup_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY apigroup
    ADD CONSTRAINT apigroup_pkey PRIMARY KEY (apigroupid);

--
-- Name: apikey apikey_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY apikey
    ADD CONSTRAINT apikey_pkey PRIMARY KEY (apikeyid);

--
-- Name: assignment assignment_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY assignment
    ADD CONSTRAINT assignment_pkey PRIMARY KEY (assignmentid);

--
-- Name: chunk chunk_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY chunk
    ADD CONSTRAINT chunk_pkey PRIMARY KEY (chunkid);

--
-- Name: config config_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY config
    ADD CONSTRAINT config_pkey PRIMARY KEY (configid);

--
-- Name: configsection configsection_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY configsection
    ADD CONSTRAINT configsection_pkey PRIMARY KEY (configsectionid);

--
-- Name: crackerbinary crackerbinary_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY crackerbinary
    ADD CONSTRAINT crackerbinary_pkey PRIMARY KEY (crackerbinaryid);

--
-- Name: crackerbinarytype crackerbinarytype_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY crackerbinarytype
    ADD CONSTRAINT crackerbinarytype_pkey PRIMARY KEY (crackerbinarytypeid);

--
-- Name: crackerbinarytype crackerbinarytype_typename_key; Type: CONSTRAINT;
--

ALTER TABLE ONLY crackerbinarytype
    ADD CONSTRAINT crackerbinarytype_typename_key UNIQUE (typename);

--
-- Name: file file_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_pkey PRIMARY KEY (fileid);

--
-- Name: filedelete filedelete_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY filedelete
    ADD CONSTRAINT filedelete_pkey PRIMARY KEY (filedeleteid);

--
-- Name: filedownload filedownload_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY filedownload
    ADD CONSTRAINT filedownload_pkey PRIMARY KEY (filedownloadid);

--
-- Name: filepretask filepretask_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY filepretask
    ADD CONSTRAINT filepretask_pkey PRIMARY KEY (filepretaskid);

--
-- Name: filetask filetask_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY filetask
    ADD CONSTRAINT filetask_pkey PRIMARY KEY (filetaskid);

--
-- Name: hash hash_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY hash
    ADD CONSTRAINT hash_pkey PRIMARY KEY (hashid);

--
-- Name: hashbinary hashbinary_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY hashbinary
    ADD CONSTRAINT hashbinary_pkey PRIMARY KEY (hashbinaryid);

--
-- Name: hashlist hashlist_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY hashlist
    ADD CONSTRAINT hashlist_pkey PRIMARY KEY (hashlistid);

--
-- Name: hashlisthashlist hashlisthashlist_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY hashlisthashlist
    ADD CONSTRAINT hashlisthashlist_pkey PRIMARY KEY (hashlisthashlistid);

--
-- Name: hashtype hashtype_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY hashtype
    ADD CONSTRAINT hashtype_pkey PRIMARY KEY (hashtypeid);

--
-- Name: healthcheck healthcheck_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY healthcheck
    ADD CONSTRAINT healthcheck_pkey PRIMARY KEY (healthcheckid);

--
-- Name: healthcheckagent healthcheckagent_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY healthcheckagent
    ADD CONSTRAINT healthcheckagent_pkey PRIMARY KEY (healthcheckagentid);

--
-- Name: htp_user htp_user_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY htp_user
    ADD CONSTRAINT htp_user_pkey PRIMARY KEY (userid);

--
-- Name: jwtapikey jwtapikey_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY jwtapikey
    ADD CONSTRAINT jwtapikey_pkey PRIMARY KEY (jwtapikeyid);

--
-- Name: logentry logentry_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY logentry
    ADD CONSTRAINT logentry_pkey PRIMARY KEY (logentryid);

--
-- Name: notificationsetting notificationsetting_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY notificationsetting
    ADD CONSTRAINT notificationsetting_pkey PRIMARY KEY (notificationsettingid);

--
-- Name: preprocessor preprocessor_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY preprocessor
    ADD CONSTRAINT preprocessor_pkey PRIMARY KEY (preprocessorid);

--
-- Name: pretask pretask_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY pretask
    ADD CONSTRAINT pretask_pkey PRIMARY KEY (pretaskid);

--
-- Name: regvoucher regvoucher_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY regvoucher
    ADD CONSTRAINT regvoucher_pkey PRIMARY KEY (regvoucherid);

--
-- Name: rightgroup rightgroup_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY rightgroup
    ADD CONSTRAINT rightgroup_pkey PRIMARY KEY (rightgroupid);

--
-- Name: session session_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY session
    ADD CONSTRAINT session_pkey PRIMARY KEY (sessionid);

--
-- Name: speed speed_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY speed
    ADD CONSTRAINT speed_pkey PRIMARY KEY (speedid);

--
-- Name: storedvalue storedvalue_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY storedvalue
    ADD CONSTRAINT storedvalue_pkey PRIMARY KEY (storedvalueid);

--
-- Name: supertask supertask_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY supertask
    ADD CONSTRAINT supertask_pkey PRIMARY KEY (supertaskid);

--
-- Name: supertaskpretask supertaskpretask_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY supertaskpretask
    ADD CONSTRAINT supertaskpretask_pkey PRIMARY KEY (supertaskpretaskid);

--
-- Name: task task_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY task
    ADD CONSTRAINT task_pkey PRIMARY KEY (taskid);

--
-- Name: taskdebugoutput taskdebugoutput_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY taskdebugoutput
    ADD CONSTRAINT taskdebugoutput_pkey PRIMARY KEY (taskdebugoutputid);

--
-- Name: taskwrapper taskwrapper_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY taskwrapper
    ADD CONSTRAINT taskwrapper_pkey PRIMARY KEY (taskwrapperid);

--
-- Name: zap zap_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY zap
    ADD CONSTRAINT zap_pkey PRIMARY KEY (zapid);

--
-- Name: accessgroupagent_accessgroupid_idx; Type: INDEX;
--

CREATE INDEX accessgroupagent_accessgroupid_idx ON accessgroupagent USING btree (accessgroupid);

--
-- Name: accessgroupagent_agentid_idx; Type: INDEX;
--

CREATE INDEX accessgroupagent_agentid_idx ON accessgroupagent USING btree (agentid);

--
-- Name: accessgroupuser_accessgroupid_idx; Type: INDEX;
--

CREATE INDEX accessgroupuser_accessgroupid_idx ON accessgroupuser USING btree (accessgroupid);

--
-- Name: accessgroupuser_userid_idx; Type: INDEX;
--

CREATE INDEX accessgroupuser_userid_idx ON accessgroupuser USING btree (userid);

--
-- Name: agent_userid_idx; Type: INDEX;
--

CREATE INDEX agent_userid_idx ON agent USING btree (userid);

--
-- Name: agenterror_agentid_idx; Type: INDEX;
--

CREATE INDEX agenterror_agentid_idx ON agenterror USING btree (agentid);

--
-- Name: agenterror_taskid_idx; Type: INDEX;
--

CREATE INDEX agenterror_taskid_idx ON agenterror USING btree (taskid);

--
-- Name: agentstat_agentid_idx; Type: INDEX;
--

CREATE INDEX agentstat_agentid_idx ON agentstat USING btree (agentid);

--
-- Name: agentzap_agentid_idx; Type: INDEX;
--

CREATE INDEX agentzap_agentid_idx ON agentzap USING btree (agentid);

--
-- Name: agentzap_lastzapid_idx; Type: INDEX;
--

CREATE INDEX agentzap_lastzapid_idx ON agentzap USING btree (lastzapid);

--
-- Name: apikey_apigroupid_idx; Type: INDEX;
--

CREATE INDEX apikey_apigroupid_idx ON apikey USING btree (apigroupid);

--
-- Name: apikey_userid_idx; Type: INDEX;
--

CREATE INDEX apikey_userid_idx ON apikey USING btree (userid);

--
-- Name: assignment_agentid_idx; Type: INDEX;
--

CREATE INDEX assignment_agentid_idx ON assignment USING btree (agentid);

--
-- Name: assignment_taskid_idx; Type: INDEX;
--

CREATE INDEX assignment_taskid_idx ON assignment USING btree (taskid);

--
-- Name: chunk_agentid_idx; Type: INDEX;
--

CREATE INDEX chunk_agentid_idx ON chunk USING btree (agentid);

--
-- Name: chunk_progress_idx; Type: INDEX;
--

CREATE INDEX chunk_progress_idx ON chunk USING btree (progress);

--
-- Name: chunk_taskid_idx; Type: INDEX;
--

CREATE INDEX chunk_taskid_idx ON chunk USING btree (taskid);

--
-- Name: config_configsectionid_idx; Type: INDEX;
--

CREATE INDEX config_configsectionid_idx ON config USING btree (configsectionid);

--
-- Name: crackerbinary_crackerbinarytypeid_idx; Type: INDEX;
--

CREATE INDEX crackerbinary_crackerbinarytypeid_idx ON crackerbinary USING btree (crackerbinarytypeid);

--
-- Name: file_accessgroupid_idx; Type: INDEX;
--

CREATE INDEX file_accessgroupid_idx ON file USING btree (accessgroupid);

--
-- Name: filepretask_fileid_idx; Type: INDEX;
--

CREATE INDEX filepretask_fileid_idx ON filepretask USING btree (fileid);

--
-- Name: filepretask_pretaskid_idx; Type: INDEX;
--

CREATE INDEX filepretask_pretaskid_idx ON filepretask USING btree (pretaskid);

--
-- Name: filetask_fileid_idx; Type: INDEX;
--

CREATE INDEX filetask_fileid_idx ON filetask USING btree (fileid);

--
-- Name: filetask_taskid_idx; Type: INDEX;
--

CREATE INDEX filetask_taskid_idx ON filetask USING btree (taskid);

--
-- Name: hash_chunkid_idx; Type: INDEX;
--

CREATE INDEX hash_chunkid_idx ON hash USING btree (chunkid);

--
-- Name: hash_hash_idx; Type: INDEX;
--

CREATE INDEX hash_hash_idx ON hash USING btree (hashtext(hash));

--
-- Name: hash_hashlistid_idx; Type: INDEX;
--

CREATE INDEX hash_hashlistid_idx ON hash USING btree (hashlistid);

--
-- Name: hash_iscracked_idx; Type: INDEX;
--

CREATE INDEX hash_iscracked_idx ON hash USING btree (iscracked);

--
-- Name: hash_timecracked_idx; Type: INDEX;
--

CREATE INDEX hash_timecracked_idx ON hash USING btree (timecracked);

--
-- Name: hashbinary_chunkid_idx; Type: INDEX;
--

CREATE INDEX hashbinary_chunkid_idx ON hashbinary USING btree (chunkid);

--
-- Name: hashbinary_hashlistid_idx; Type: INDEX;
--

CREATE INDEX hashbinary_hashlistid_idx ON hashbinary USING btree (hashlistid);

--
-- Name: hashlist_accessgroupid_idx; Type: INDEX;
--

CREATE INDEX hashlist_accessgroupid_idx ON hashlist USING btree (accessgroupid);

--
-- Name: hashlist_hashtypeid_idx; Type: INDEX;
--

CREATE INDEX hashlist_hashtypeid_idx ON hashlist USING btree (hashtypeid);

--
-- Name: hashlist_isarchived_idx; Type: INDEX;
--

CREATE INDEX hashlist_isarchived_idx ON hashlist USING btree (isarchived, hashlistid);

--
-- Name: hashlisthashlist_hashlistid_idx; Type: INDEX;
--

CREATE INDEX hashlisthashlist_hashlistid_idx ON hashlisthashlist USING btree (hashlistid);

--
-- Name: hashlisthashlist_parenthashlistid_idx; Type: INDEX;
--

CREATE INDEX hashlisthashlist_parenthashlistid_idx ON hashlisthashlist USING btree (parenthashlistid);

--
-- Name: healthcheck_crackerbinaryid_idx; Type: INDEX;
--

CREATE INDEX healthcheck_crackerbinaryid_idx ON healthcheck USING btree (crackerbinaryid);

--
-- Name: healthcheckagent_agentid_idx; Type: INDEX;
--

CREATE INDEX healthcheckagent_agentid_idx ON healthcheckagent USING btree (agentid);

--
-- Name: healthcheckagent_healthcheckid_idx; Type: INDEX;
--

CREATE INDEX healthcheckagent_healthcheckid_idx ON healthcheckagent USING btree (healthcheckid);

--
-- Name: htp_user_rightgroupid_idx; Type: INDEX;
--

CREATE INDEX htp_user_rightgroupid_idx ON htp_user USING btree (rightgroupid);

--
-- Name: idx_jwtapikey_userid; Type: INDEX;
--

CREATE INDEX idx_jwtapikey_userid ON jwtapikey USING btree (userid);

--
-- Name: notificationsetting_userid_idx; Type: INDEX;
--

CREATE INDEX notificationsetting_userid_idx ON notificationsetting USING btree (userid);

--
-- Name: pretask_crackerbinarytypeid_idx; Type: INDEX;
--

CREATE INDEX pretask_crackerbinarytypeid_idx ON pretask USING btree (crackerbinarytypeid);

--
-- Name: session_userid_idx; Type: INDEX;
--

CREATE INDEX session_userid_idx ON session USING btree (userid);

--
-- Name: speed_agentid_idx; Type: INDEX;
--

CREATE INDEX speed_agentid_idx ON speed USING btree (agentid);

--
-- Name: speed_taskid_idx; Type: INDEX;
--

CREATE INDEX speed_taskid_idx ON speed USING btree (taskid);

--
-- Name: supertaskpretask_pretaskid_idx; Type: INDEX;
--

CREATE INDEX supertaskpretask_pretaskid_idx ON supertaskpretask USING btree (pretaskid);

--
-- Name: supertaskpretask_supertaskid_idx; Type: INDEX;
--

CREATE INDEX supertaskpretask_supertaskid_idx ON supertaskpretask USING btree (supertaskid);

--
-- Name: task_crackerbinaryid_idx; Type: INDEX;
--

CREATE INDEX task_crackerbinaryid_idx ON task USING btree (crackerbinaryid);

--
-- Name: task_crackerbinarytypeid_idx; Type: INDEX;
--

CREATE INDEX task_crackerbinarytypeid_idx ON task USING btree (crackerbinarytypeid);

--
-- Name: task_isarchived_priority_taskid_idx; Type: INDEX;
--

CREATE INDEX task_isarchived_priority_taskid_idx ON task USING btree (isarchived, priority DESC, taskid);

--
-- Name: task_taskwrapperid_idx; Type: INDEX;
--

CREATE INDEX task_taskwrapperid_idx ON task USING btree (taskwrapperid);

--
-- Name: taskdebugoutput_taskid_idx; Type: INDEX;
--

CREATE INDEX taskdebugoutput_taskid_idx ON taskdebugoutput USING btree (taskid);

--
-- Name: taskwrapper_accessgroupid_idx; Type: INDEX;
--

CREATE INDEX taskwrapper_accessgroupid_idx ON taskwrapper USING btree (accessgroupid);

--
-- Name: taskwrapper_hashlistid_idx; Type: INDEX;
--

CREATE INDEX taskwrapper_hashlistid_idx ON taskwrapper USING btree (hashlistid);

--
-- Name: taskwrapper_isarchived_priority_taskwrapperid_idx; Type: INDEX;
--

CREATE INDEX taskwrapper_isarchived_priority_taskwrapperid_idx ON taskwrapper USING btree (isarchived, priority DESC, taskwrapperid);

--
-- Name: taskwrapper_priority_idx; Type: INDEX;
--

CREATE INDEX taskwrapper_priority_idx ON taskwrapper USING btree (priority);

--
-- Name: zap_agentid_idx; Type: INDEX;
--

CREATE INDEX zap_agentid_idx ON zap USING btree (agentid);

--
-- Name: zap_hashlistid_idx; Type: INDEX;
--

CREATE INDEX zap_hashlistid_idx ON zap USING btree (hashlistid);

--
-- Name: accessgroupagent accessgroupagent_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY accessgroupagent
    ADD CONSTRAINT accessgroupagent_ibfk_1 FOREIGN KEY (accessgroupid) REFERENCES accessgroup(accessgroupid);

--
-- Name: accessgroupagent accessgroupagent_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY accessgroupagent
    ADD CONSTRAINT accessgroupagent_ibfk_2 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: accessgroupuser accessgroupuser_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY accessgroupuser
    ADD CONSTRAINT accessgroupuser_ibfk_1 FOREIGN KEY (accessgroupid) REFERENCES accessgroup(accessgroupid);

--
-- Name: accessgroupuser accessgroupuser_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY accessgroupuser
    ADD CONSTRAINT accessgroupuser_ibfk_2 FOREIGN KEY (userid) REFERENCES htp_user(userid);

--
-- Name: agent agent_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT agent_ibfk_1 FOREIGN KEY (userid) REFERENCES htp_user(userid);

--
-- Name: agenterror agenterror_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY agenterror
    ADD CONSTRAINT agenterror_ibfk_1 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: agenterror agenterror_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY agenterror
    ADD CONSTRAINT agenterror_ibfk_2 FOREIGN KEY (taskid) REFERENCES task(taskid);

--
-- Name: agentstat agentstat_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY agentstat
    ADD CONSTRAINT agentstat_ibfk_1 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: agentzap agentzap_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY agentzap
    ADD CONSTRAINT agentzap_ibfk_1 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: agentzap agentzap_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY agentzap
    ADD CONSTRAINT agentzap_ibfk_2 FOREIGN KEY (lastzapid) REFERENCES zap(zapid);

--
-- Name: apikey apikey_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY apikey
    ADD CONSTRAINT apikey_ibfk_1 FOREIGN KEY (userid) REFERENCES htp_user(userid);

--
-- Name: apikey apikey_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY apikey
    ADD CONSTRAINT apikey_ibfk_2 FOREIGN KEY (apigroupid) REFERENCES apigroup(apigroupid);

--
-- Name: assignment assignment_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY assignment
    ADD CONSTRAINT assignment_ibfk_1 FOREIGN KEY (taskid) REFERENCES task(taskid);

--
-- Name: assignment assignment_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY assignment
    ADD CONSTRAINT assignment_ibfk_2 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: chunk chunk_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY chunk
    ADD CONSTRAINT chunk_ibfk_1 FOREIGN KEY (taskid) REFERENCES task(taskid);

--
-- Name: chunk chunk_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY chunk
    ADD CONSTRAINT chunk_ibfk_2 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: config config_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY config
    ADD CONSTRAINT config_ibfk_1 FOREIGN KEY (configsectionid) REFERENCES configsection(configsectionid);

--
-- Name: crackerbinary crackerbinary_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY crackerbinary
    ADD CONSTRAINT crackerbinary_ibfk_1 FOREIGN KEY (crackerbinarytypeid) REFERENCES crackerbinarytype(crackerbinarytypeid);

--
-- Name: file file_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_ibfk_1 FOREIGN KEY (accessgroupid) REFERENCES accessgroup(accessgroupid);

--
-- Name: filepretask filepretask_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY filepretask
    ADD CONSTRAINT filepretask_ibfk_1 FOREIGN KEY (fileid) REFERENCES file(fileid);

--
-- Name: filepretask filepretask_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY filepretask
    ADD CONSTRAINT filepretask_ibfk_2 FOREIGN KEY (pretaskid) REFERENCES pretask(pretaskid);

--
-- Name: filetask filetask_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY filetask
    ADD CONSTRAINT filetask_ibfk_1 FOREIGN KEY (fileid) REFERENCES file(fileid);

--
-- Name: filetask filetask_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY filetask
    ADD CONSTRAINT filetask_ibfk_2 FOREIGN KEY (taskid) REFERENCES task(taskid);

--
-- Name: jwtapikey fk_jwtapikey_user; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY jwtapikey
    ADD CONSTRAINT fk_jwtapikey_user FOREIGN KEY (userid) REFERENCES htp_user(userid);

--
-- Name: hash hash_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hash
    ADD CONSTRAINT hash_ibfk_1 FOREIGN KEY (hashlistid) REFERENCES hashlist(hashlistid);

--
-- Name: hash hash_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hash
    ADD CONSTRAINT hash_ibfk_2 FOREIGN KEY (chunkid) REFERENCES chunk(chunkid);

--
-- Name: hashbinary hashbinary_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hashbinary
    ADD CONSTRAINT hashbinary_ibfk_1 FOREIGN KEY (hashlistid) REFERENCES hashlist(hashlistid);

--
-- Name: hashbinary hashbinary_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hashbinary
    ADD CONSTRAINT hashbinary_ibfk_2 FOREIGN KEY (chunkid) REFERENCES chunk(chunkid);

--
-- Name: hashlist hashlist_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hashlist
    ADD CONSTRAINT hashlist_ibfk_1 FOREIGN KEY (hashtypeid) REFERENCES hashtype(hashtypeid);

--
-- Name: hashlist hashlist_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hashlist
    ADD CONSTRAINT hashlist_ibfk_2 FOREIGN KEY (accessgroupid) REFERENCES accessgroup(accessgroupid);

--
-- Name: hashlisthashlist hashlisthashlist_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hashlisthashlist
    ADD CONSTRAINT hashlisthashlist_ibfk_1 FOREIGN KEY (parenthashlistid) REFERENCES hashlist(hashlistid);

--
-- Name: hashlisthashlist hashlisthashlist_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY hashlisthashlist
    ADD CONSTRAINT hashlisthashlist_ibfk_2 FOREIGN KEY (hashlistid) REFERENCES hashlist(hashlistid);

--
-- Name: healthcheck healthcheck_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY healthcheck
    ADD CONSTRAINT healthcheck_ibfk_1 FOREIGN KEY (crackerbinaryid) REFERENCES crackerbinary(crackerbinaryid);

--
-- Name: healthcheckagent healthcheckagent_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY healthcheckagent
    ADD CONSTRAINT healthcheckagent_ibfk_1 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: healthcheckagent healthcheckagent_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY healthcheckagent
    ADD CONSTRAINT healthcheckagent_ibfk_2 FOREIGN KEY (healthcheckid) REFERENCES healthcheck(healthcheckid);

--
-- Name: notificationsetting notificationsetting_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY notificationsetting
    ADD CONSTRAINT notificationsetting_ibfk_1 FOREIGN KEY (userid) REFERENCES htp_user(userid);

--
-- Name: pretask pretask_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY pretask
    ADD CONSTRAINT pretask_ibfk_1 FOREIGN KEY (crackerbinarytypeid) REFERENCES crackerbinarytype(crackerbinarytypeid);

--
-- Name: session session_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY session
    ADD CONSTRAINT session_ibfk_1 FOREIGN KEY (userid) REFERENCES htp_user(userid);

--
-- Name: speed speed_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY speed
    ADD CONSTRAINT speed_ibfk_1 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: speed speed_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY speed
    ADD CONSTRAINT speed_ibfk_2 FOREIGN KEY (taskid) REFERENCES task(taskid);

--
-- Name: supertaskpretask supertaskpretask_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY supertaskpretask
    ADD CONSTRAINT supertaskpretask_ibfk_1 FOREIGN KEY (supertaskid) REFERENCES supertask(supertaskid);

--
-- Name: supertaskpretask supertaskpretask_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY supertaskpretask
    ADD CONSTRAINT supertaskpretask_ibfk_2 FOREIGN KEY (pretaskid) REFERENCES pretask(pretaskid);

--
-- Name: task task_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY task
    ADD CONSTRAINT task_ibfk_1 FOREIGN KEY (crackerbinaryid) REFERENCES crackerbinary(crackerbinaryid);

--
-- Name: task task_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY task
    ADD CONSTRAINT task_ibfk_2 FOREIGN KEY (crackerbinarytypeid) REFERENCES crackerbinarytype(crackerbinarytypeid);

--
-- Name: task task_ibfk_3; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY task
    ADD CONSTRAINT task_ibfk_3 FOREIGN KEY (taskwrapperid) REFERENCES taskwrapper(taskwrapperid);

--
-- Name: taskdebugoutput taskdebugoutput_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY taskdebugoutput
    ADD CONSTRAINT taskdebugoutput_ibfk_1 FOREIGN KEY (taskid) REFERENCES task(taskid);

--
-- Name: taskwrapper taskwrapper_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY taskwrapper
    ADD CONSTRAINT taskwrapper_ibfk_1 FOREIGN KEY (hashlistid) REFERENCES hashlist(hashlistid);

--
-- Name: taskwrapper taskwrapper_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY taskwrapper
    ADD CONSTRAINT taskwrapper_ibfk_2 FOREIGN KEY (accessgroupid) REFERENCES accessgroup(accessgroupid);

--
-- Name: htp_user user_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY htp_user
    ADD CONSTRAINT user_ibfk_1 FOREIGN KEY (rightgroupid) REFERENCES rightgroup(rightgroupid);

--
-- Name: zap zap_ibfk_1; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY zap
    ADD CONSTRAINT zap_ibfk_1 FOREIGN KEY (agentid) REFERENCES agent(agentid);

--
-- Name: zap zap_ibfk_2; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY zap
    ADD CONSTRAINT zap_ibfk_2 FOREIGN KEY (hashlistid) REFERENCES hashlist(hashlistid);
