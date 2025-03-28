# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#   * Rearrange models' order
#   * Make sure each model has one field with primary_key=True
#   * Make sure each ForeignKey and OneToOneField has `on_delete` set to the desired behavior
#   * Remove `managed = False` lines if you wish to allow Django to create, modify, and delete the table
# Feel free to rename the models, but don't rename db_table values or field names.
#from django.db import models

class models:
    DO_NOTHING = 'do_nothing'
    class Field:
        def __init__(self, **kwargs):
            pass
    class Model:
        pass
    class AutoField(Field):
        pass

    class CharField(Field):
        pass
    class IntegerField(Field):
        pass
    class TextField(Field):
        pass
    class BigIntegerField(Field):
        pass
    class ForeignKey(Field):
        def __init__(self, related_model, on_cascade, **kwargs):
            pass

class Accessgroup(models.Model):
    accessgroupid = models.AutoField(db_column='accessGroupId', primary_key=True)
    groupname = models.CharField(db_column='groupName', max_length=50)

    class Meta:
        managed = False
        db_table = 'AccessGroup'


class Accessgroupagent(models.Model):
    accessgroupagentid = models.AutoField(db_column='accessGroupAgentId', primary_key=True)
    accessgroupid = models.ForeignKey(Accessgroup, models.DO_NOTHING, db_column='accessGroupId')
    agentid = models.ForeignKey('Agent', models.DO_NOTHING, db_column='agentId')

    class Meta:
        managed = False
        db_table = 'AccessGroupAgent'


class Accessgroupuser(models.Model):
    accessgroupuserid = models.AutoField(db_column='accessGroupUserId', primary_key=True)
    accessgroupid = models.ForeignKey(Accessgroup, models.DO_NOTHING, db_column='accessGroupId')
    userid = models.ForeignKey('User', models.DO_NOTHING, db_column='userId')

    class Meta:
        managed = False
        db_table = 'AccessGroupUser'


class Agent(models.Model):
    agentid = models.AutoField(db_column='agentId', primary_key=True)
    agentname = models.CharField(db_column='agentName', max_length=100)
    uid = models.CharField(max_length=100)
    os = models.IntegerField()
    devices = models.TextField()
    cmdpars = models.CharField(db_column='cmdPars', max_length=256)
    ignoreerrors = models.IntegerField(db_column='ignoreErrors')
    isactive = models.IntegerField(db_column='isActive')
    istrusted = models.IntegerField(db_column='isTrusted')
    token = models.CharField(max_length=30)
    lastact = models.CharField(db_column='lastAct', max_length=50)
    lasttime = models.BigIntegerField(db_column='lastTime')
    lastip = models.CharField(db_column='lastIp', max_length=50)
    userid = models.ForeignKey('User', models.DO_NOTHING, db_column='userId', blank=True, null=True)
    cpuonly = models.IntegerField(db_column='cpuOnly')
    clientsignature = models.CharField(db_column='clientSignature', max_length=50)

    class Meta:
        managed = False
        db_table = 'Agent'


class Agentbinary(models.Model):
    agentbinaryid = models.AutoField(db_column='agentBinaryId', primary_key=True)
    type = models.CharField(max_length=20)
    version = models.CharField(max_length=20)
    operatingsystems = models.CharField(db_column='operatingSystems', max_length=50)
    filename = models.CharField(max_length=50)
    updatetrack = models.CharField(db_column='updateTrack', max_length=20)
    updateavailable = models.CharField(db_column='updateAvailable', max_length=20)

    class Meta:
        managed = False
        db_table = 'AgentBinary'


class Agenterror(models.Model):
    agenterrorid = models.AutoField(db_column='agentErrorId', primary_key=True)
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId')
    taskid = models.ForeignKey('Task', models.DO_NOTHING, db_column='taskId', blank=True, null=True)
    time = models.BigIntegerField()
    error = models.TextField()
    chunkid = models.IntegerField(db_column='chunkId', blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'AgentError'


class Agentstat(models.Model):
    agentstatid = models.AutoField(db_column='agentStatId', primary_key=True)
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId')
    stattype = models.IntegerField(db_column='statType')
    time = models.BigIntegerField()
    value = models.CharField(max_length=128)

    class Meta:
        managed = False
        db_table = 'AgentStat'


class Agentzap(models.Model):
    agentzapid = models.AutoField(db_column='agentZapId', primary_key=True)
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId')
    lastzapid = models.ForeignKey('Zap', models.DO_NOTHING, db_column='lastZapId', blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'AgentZap'


class Apigroup(models.Model):
    apigroupid = models.AutoField(db_column='apiGroupId', primary_key=True)
    name = models.CharField(max_length=100)
    permissions = models.TextField()

    class Meta:
        managed = False
        db_table = 'ApiGroup'


class Apikey(models.Model):
    apikeyid = models.AutoField(db_column='apiKeyId', primary_key=True)
    startvalid = models.BigIntegerField(db_column='startValid')
    endvalid = models.BigIntegerField(db_column='endValid')
    accesskey = models.CharField(db_column='accessKey', max_length=256)
    accesscount = models.IntegerField(db_column='accessCount')
    userid = models.ForeignKey('User', models.DO_NOTHING, db_column='userId')
    apigroupid = models.ForeignKey(Apigroup, models.DO_NOTHING, db_column='apiGroupId')

    class Meta:
        managed = False
        db_table = 'ApiKey'


class Assignment(models.Model):
    assignmentid = models.AutoField(db_column='assignmentId', primary_key=True)
    taskid = models.ForeignKey('Task', models.DO_NOTHING, db_column='taskId')
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId')
    benchmark = models.CharField(max_length=50)

    class Meta:
        managed = False
        db_table = 'Assignment'


class Chunk(models.Model):
    chunkid = models.AutoField(db_column='chunkId', primary_key=True)
    taskid = models.ForeignKey('Task', models.DO_NOTHING, db_column='taskId')
    skip = models.PositiveBigIntegerField()
    length = models.PositiveBigIntegerField()
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId', blank=True, null=True)
    dispatchtime = models.BigIntegerField(db_column='dispatchTime')
    solvetime = models.BigIntegerField(db_column='solveTime')
    checkpoint = models.PositiveBigIntegerField()
    progress = models.IntegerField(blank=True, null=True)
    state = models.IntegerField()
    cracked = models.IntegerField()
    speed = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'Chunk'


class Config(models.Model):
    configid = models.AutoField(db_column='configId', primary_key=True)
    configsectionid = models.ForeignKey('Configsection', models.DO_NOTHING, db_column='configSectionId')
    item = models.CharField(max_length=80)
    value = models.TextField()

    class Meta:
        managed = False
        db_table = 'Config'


class Configsection(models.Model):
    configsectionid = models.AutoField(db_column='configSectionId', primary_key=True)
    sectionname = models.CharField(db_column='sectionName', max_length=100)

    class Meta:
        managed = False
        db_table = 'ConfigSection'


class Crackerbinary(models.Model):
    crackerbinaryid = models.AutoField(db_column='crackerBinaryId', primary_key=True)
    crackerbinarytypeid = models.ForeignKey('Crackerbinarytype', models.DO_NOTHING, db_column='crackerBinaryTypeId')
    version = models.CharField(max_length=20)
    downloadurl = models.CharField(db_column='downloadUrl', max_length=150)
    binaryname = models.CharField(db_column='binaryName', max_length=50)

    class Meta:
        managed = False
        db_table = 'CrackerBinary'


class Crackerbinarytype(models.Model):
    crackerbinarytypeid = models.AutoField(db_column='crackerBinaryTypeId', primary_key=True)
    typename = models.CharField(db_column='typeName', max_length=30)
    ischunkingavailable = models.IntegerField(db_column='isChunkingAvailable')

    class Meta:
        managed = False
        db_table = 'CrackerBinaryType'


class File(models.Model):
    fileid = models.AutoField(db_column='fileId', primary_key=True)
    filename = models.CharField(max_length=100)
    size = models.BigIntegerField()
    issecret = models.IntegerField(db_column='isSecret')
    filetype = models.IntegerField(db_column='fileType')
    accessgroupid = models.ForeignKey(Accessgroup, models.DO_NOTHING, db_column='accessGroupId')
    linecount = models.BigIntegerField(db_column='lineCount', blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'File'


class Filedelete(models.Model):
    filedeleteid = models.AutoField(db_column='fileDeleteId', primary_key=True)
    filename = models.CharField(max_length=256)
    time = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'FileDelete'


class Filedownload(models.Model):
    filedownloadid = models.AutoField(db_column='fileDownloadId', primary_key=True)
    time = models.BigIntegerField()
    fileid = models.ForeignKey(File, models.DO_NOTHING, db_column='fileId')
    status = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'FileDownload'


class Filepretask(models.Model):
    filepretaskid = models.AutoField(db_column='filePretaskId', primary_key=True)
    fileid = models.ForeignKey(File, models.DO_NOTHING, db_column='fileId')
    pretaskid = models.ForeignKey('Pretask', models.DO_NOTHING, db_column='pretaskId')

    class Meta:
        managed = False
        db_table = 'FilePretask'


class Filetask(models.Model):
    filetaskid = models.AutoField(db_column='fileTaskId', primary_key=True)
    fileid = models.ForeignKey(File, models.DO_NOTHING, db_column='fileId')
    taskid = models.ForeignKey('Task', models.DO_NOTHING, db_column='taskId')

    class Meta:
        managed = False
        db_table = 'FileTask'


class Hash(models.Model):
    hashid = models.AutoField(db_column='hashId', primary_key=True)
    hashlistid = models.ForeignKey('Hashlist', models.DO_NOTHING, db_column='hashlistId')
    hash = models.TextField()
    salt = models.CharField(max_length=256, blank=True, null=True)
    plaintext = models.CharField(max_length=256, blank=True, null=True)
    timecracked = models.BigIntegerField(db_column='timeCracked', blank=True, null=True)
    chunkid = models.ForeignKey(Chunk, models.DO_NOTHING, db_column='chunkId', blank=True, null=True)
    iscracked = models.IntegerField(db_column='isCracked')
    crackpos = models.BigIntegerField(db_column='crackPos')

    class Meta:
        managed = False
        db_table = 'Hash'


class Hashbinary(models.Model):
    hashbinaryid = models.AutoField(db_column='hashBinaryId', primary_key=True)
    hashlistid = models.ForeignKey('Hashlist', models.DO_NOTHING, db_column='hashlistId')
    essid = models.CharField(max_length=100)
    hash = models.TextField()
    plaintext = models.CharField(max_length=1024, blank=True, null=True)
    timecracked = models.BigIntegerField(db_column='timeCracked', blank=True, null=True)
    chunkid = models.ForeignKey(Chunk, models.DO_NOTHING, db_column='chunkId', blank=True, null=True)
    iscracked = models.IntegerField(db_column='isCracked')
    crackpos = models.BigIntegerField(db_column='crackPos')

    class Meta:
        managed = False
        db_table = 'HashBinary'


class Hashtype(models.Model):
    hashtypeid = models.IntegerField(db_column='hashTypeId', primary_key=True)
    description = models.CharField(max_length=256)
    issalted = models.IntegerField(db_column='isSalted')
    isslowhash = models.IntegerField(db_column='isSlowHash')

    class Meta:
        managed = False
        db_table = 'HashType'


class Hashlist(models.Model):
    hashlistid = models.AutoField(db_column='hashlistId', primary_key=True)
    hashlistname = models.CharField(db_column='hashlistName', max_length=100)
    format = models.IntegerField()
    hashtypeid = models.ForeignKey(Hashtype, models.DO_NOTHING, db_column='hashTypeId')
    hashcount = models.IntegerField(db_column='hashCount')
    saltseparator = models.CharField(db_column='saltSeparator', max_length=10, blank=True, null=True)
    cracked = models.IntegerField()
    issecret = models.IntegerField(db_column='isSecret')
    hexsalt = models.IntegerField(db_column='hexSalt')
    issalted = models.IntegerField(db_column='isSalted')
    accessgroupid = models.ForeignKey(Accessgroup, models.DO_NOTHING, db_column='accessGroupId')
    notes = models.TextField()
    brainid = models.IntegerField(db_column='brainId')
    brainfeatures = models.IntegerField(db_column='brainFeatures')
    isarchived = models.IntegerField(db_column='isArchived')

    class Meta:
        managed = False
        db_table = 'Hashlist'


class Hashlisthashlist(models.Model):
    hashlisthashlistid = models.AutoField(db_column='hashlistHashlistId', primary_key=True)
    parenthashlistid = models.ForeignKey(Hashlist, models.DO_NOTHING, db_column='parentHashlistId')
    hashlistid = models.ForeignKey(Hashlist, models.DO_NOTHING, db_column='hashlistId')

    class Meta:
        managed = False
        db_table = 'HashlistHashlist'


class Healthcheck(models.Model):
    healthcheckid = models.AutoField(db_column='healthCheckId', primary_key=True)
    time = models.BigIntegerField()
    status = models.IntegerField()
    checktype = models.IntegerField(db_column='checkType')
    hashtypeid = models.IntegerField(db_column='hashtypeId')
    crackerbinaryid = models.ForeignKey(Crackerbinary, models.DO_NOTHING, db_column='crackerBinaryId')
    expectedcracks = models.IntegerField(db_column='expectedCracks')
    attackcmd = models.CharField(db_column='attackCmd', max_length=256)

    class Meta:
        managed = False
        db_table = 'HealthCheck'


class Healthcheckagent(models.Model):
    healthcheckagentid = models.AutoField(db_column='healthCheckAgentId', primary_key=True)
    healthcheckid = models.ForeignKey(Healthcheck, models.DO_NOTHING, db_column='healthCheckId')
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId')
    status = models.IntegerField()
    cracked = models.IntegerField()
    numgpus = models.IntegerField(db_column='numGpus')
    start = models.BigIntegerField()
    end = models.BigIntegerField()
    errors = models.TextField()

    class Meta:
        managed = False
        db_table = 'HealthCheckAgent'


class Logentry(models.Model):
    logentryid = models.AutoField(db_column='logEntryId', primary_key=True)
    issuer = models.CharField(max_length=50)
    issuerid = models.CharField(db_column='issuerId', max_length=50)
    level = models.CharField(max_length=50)
    message = models.TextField()
    time = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'LogEntry'


class Notificationsetting(models.Model):
    notificationsettingid = models.AutoField(db_column='notificationSettingId', primary_key=True)
    action = models.CharField(max_length=50)
    objectid = models.IntegerField(db_column='objectId', blank=True, null=True)
    notification = models.CharField(max_length=50)
    userid = models.ForeignKey('User', models.DO_NOTHING, db_column='userId')
    receiver = models.CharField(max_length=256)
    isactive = models.IntegerField(db_column='isActive')

    class Meta:
        managed = False
        db_table = 'NotificationSetting'


class Preprocessor(models.Model):
    preprocessorid = models.AutoField(db_column='preprocessorId', primary_key=True)
    name = models.CharField(max_length=256)
    url = models.CharField(max_length=512)
    binaryname = models.CharField(db_column='binaryName', max_length=256)
    keyspacecommand = models.CharField(db_column='keyspaceCommand', max_length=256, blank=True, null=True)
    skipcommand = models.CharField(db_column='skipCommand', max_length=256, blank=True, null=True)
    limitcommand = models.CharField(db_column='limitCommand', max_length=256, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'Preprocessor'


class Pretask(models.Model):
    pretaskid = models.AutoField(db_column='pretaskId', primary_key=True)
    taskname = models.CharField(db_column='taskName', max_length=100)
    attackcmd = models.CharField(db_column='attackCmd', max_length=256)
    chunktime = models.IntegerField(db_column='chunkTime')
    statustimer = models.IntegerField(db_column='statusTimer')
    color = models.CharField(max_length=20, blank=True, null=True)
    issmall = models.IntegerField(db_column='isSmall')
    iscputask = models.IntegerField(db_column='isCpuTask')
    usenewbench = models.IntegerField(db_column='useNewBench')
    priority = models.IntegerField()
    maxagents = models.IntegerField(db_column='maxAgents')
    ismaskimport = models.IntegerField(db_column='isMaskImport')
    crackerbinarytypeid = models.ForeignKey(Crackerbinarytype, models.DO_NOTHING, db_column='crackerBinaryTypeId')

    class Meta:
        managed = False
        db_table = 'Pretask'


class Regvoucher(models.Model):
    regvoucherid = models.AutoField(db_column='regVoucherId', primary_key=True)
    voucher = models.CharField(max_length=100)
    time = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'RegVoucher'


class Rightgroup(models.Model):
    rightgroupid = models.AutoField(db_column='rightGroupId', primary_key=True)
    groupname = models.CharField(db_column='groupName', max_length=50)
    permissions = models.TextField()

    class Meta:
        managed = False
        db_table = 'RightGroup'


class Session(models.Model):
    sessionid = models.AutoField(db_column='sessionId', primary_key=True)
    userid = models.ForeignKey('User', models.DO_NOTHING, db_column='userId')
    sessionstartdate = models.BigIntegerField(db_column='sessionStartDate')
    lastactiondate = models.BigIntegerField(db_column='lastActionDate')
    isopen = models.IntegerField(db_column='isOpen')
    sessionlifetime = models.IntegerField(db_column='sessionLifetime')
    sessionkey = models.CharField(db_column='sessionKey', max_length=256)

    class Meta:
        managed = False
        db_table = 'Session'


class Speed(models.Model):
    speedid = models.AutoField(db_column='speedId', primary_key=True)
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId')
    taskid = models.ForeignKey('Task', models.DO_NOTHING, db_column='taskId')
    speed = models.BigIntegerField()
    time = models.BigIntegerField()

    class Meta:
        managed = False
        db_table = 'Speed'


class Storedvalue(models.Model):
    storedvalueid = models.CharField(db_column='storedValueId', primary_key=True, max_length=50)
    val = models.CharField(max_length=256)

    class Meta:
        managed = False
        db_table = 'StoredValue'


class Supertask(models.Model):
    supertaskid = models.AutoField(db_column='supertaskId', primary_key=True)
    supertaskname = models.CharField(db_column='supertaskName', max_length=50)

    class Meta:
        managed = False
        db_table = 'Supertask'


class Supertaskpretask(models.Model):
    supertaskpretaskid = models.AutoField(db_column='supertaskPretaskId', primary_key=True)
    supertaskid = models.ForeignKey(Supertask, models.DO_NOTHING, db_column='supertaskId')
    pretaskid = models.ForeignKey(Pretask, models.DO_NOTHING, db_column='pretaskId')

    class Meta:
        managed = False
        db_table = 'SupertaskPretask'


class Task(models.Model):
    taskid = models.AutoField(db_column='taskId', primary_key=True)
    taskname = models.CharField(db_column='taskName', max_length=256)
    attackcmd = models.CharField(db_column='attackCmd', max_length=256)
    chunktime = models.IntegerField(db_column='chunkTime')
    statustimer = models.IntegerField(db_column='statusTimer')
    keyspace = models.BigIntegerField()
    keyspaceprogress = models.BigIntegerField(db_column='keyspaceProgress')
    priority = models.IntegerField()
    maxagents = models.IntegerField(db_column='maxAgents')
    color = models.CharField(max_length=20, blank=True, null=True)
    issmall = models.IntegerField(db_column='isSmall')
    iscputask = models.IntegerField(db_column='isCpuTask')
    usenewbench = models.IntegerField(db_column='useNewBench')
    skipkeyspace = models.BigIntegerField(db_column='skipKeyspace')
    crackerbinaryid = models.ForeignKey(Crackerbinary, models.DO_NOTHING, db_column='crackerBinaryId', blank=True, null=True)
    crackerbinarytypeid = models.ForeignKey(Crackerbinarytype, models.DO_NOTHING, db_column='crackerBinaryTypeId', blank=True, null=True)
    taskwrapperid = models.ForeignKey('Taskwrapper', models.DO_NOTHING, db_column='taskWrapperId')
    isarchived = models.IntegerField(db_column='isArchived')
    notes = models.TextField()
    staticchunks = models.IntegerField(db_column='staticChunks')
    chunksize = models.BigIntegerField(db_column='chunkSize')
    forcepipe = models.IntegerField(db_column='forcePipe')
    usepreprocessor = models.IntegerField(db_column='usePreprocessor')
    preprocessorcommand = models.CharField(db_column='preprocessorCommand', max_length=256)

    class Meta:
        managed = False
        db_table = 'Task'


class Taskdebugoutput(models.Model):
    taskdebugoutputid = models.AutoField(db_column='taskDebugOutputId', primary_key=True)
    taskid = models.ForeignKey(Task, models.DO_NOTHING, db_column='taskId')
    output = models.CharField(max_length=256)

    class Meta:
        managed = False
        db_table = 'TaskDebugOutput'


class Taskwrapper(models.Model):
    taskwrapperid = models.AutoField(db_column='taskWrapperId', primary_key=True)
    priority = models.IntegerField()
    maxagents = models.IntegerField(db_column='maxAgents')
    tasktype = models.IntegerField(db_column='taskType')
    hashlistid = models.ForeignKey(Hashlist, models.DO_NOTHING, db_column='hashlistId')
    accessgroupid = models.ForeignKey(Accessgroup, models.DO_NOTHING, db_column='accessGroupId', blank=True, null=True)
    taskwrappername = models.CharField(db_column='taskWrapperName', max_length=100)
    isarchived = models.IntegerField(db_column='isArchived')
    cracked = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'TaskWrapper'


class User(models.Model):
    userid = models.AutoField(db_column='userId', primary_key=True)
    username = models.CharField(unique=True, max_length=100)
    email = models.CharField(max_length=150)
    passwordhash = models.CharField(db_column='passwordHash', max_length=256)
    passwordsalt = models.CharField(db_column='passwordSalt', max_length=256)
    isvalid = models.IntegerField(db_column='isValid')
    iscomputedpassword = models.IntegerField(db_column='isComputedPassword')
    lastlogindate = models.BigIntegerField(db_column='lastLoginDate')
    registeredsince = models.BigIntegerField(db_column='registeredSince')
    sessionlifetime = models.IntegerField(db_column='sessionLifetime')
    rightgroupid = models.ForeignKey(Rightgroup, models.DO_NOTHING, db_column='rightGroupId')
    yubikey = models.CharField(max_length=256, blank=True, null=True)
    otp1 = models.CharField(max_length=256, blank=True, null=True)
    otp2 = models.CharField(max_length=256, blank=True, null=True)
    otp3 = models.CharField(max_length=256, blank=True, null=True)
    otp4 = models.CharField(max_length=256, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'User'


class Zap(models.Model):
    zapid = models.AutoField(db_column='zapId', primary_key=True)
    hash = models.TextField()
    solvetime = models.BigIntegerField(db_column='solveTime')
    agentid = models.ForeignKey(Agent, models.DO_NOTHING, db_column='agentId', blank=True, null=True)
    hashlistid = models.ForeignKey(Hashlist, models.DO_NOTHING, db_column='hashlistId')

    class Meta:
        managed = False
        db_table = 'Zap'
