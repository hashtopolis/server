-- define stored procedures to create/drop indexes only if they don't exist yet
DROP PROCEDURE IF EXISTS `CreateIndex`;
CREATE PROCEDURE `CreateIndex`
(
    IN given_table    VARCHAR(64),
    IN given_index    VARCHAR(64),
    IN given_columns  VARCHAR(64)
)
BEGIN

    DECLARE IndexIsThere INTEGER;

    SELECT COUNT(1) INTO IndexIsThere
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_name   = given_table
    AND   index_name   = given_index;

    IF IndexIsThere = 0 THEN
        SET @sqlstmt = CONCAT('CREATE INDEX ', given_index, ' ON ', given_table, ' (', given_columns, ')');
        PREPARE st FROM @sqlstmt;
        EXECUTE st;
        DEALLOCATE PREPARE st;
    ELSE
        SELECT CONCAT('Index ', given_index, ' already exists on Table ', given_table) CreateindexErrorMessage;
    END IF;

END;

DROP PROCEDURE IF EXISTS `DropIndex`;
CREATE PROCEDURE `DropIndex`
(
    IN given_table    VARCHAR(64),
    IN given_index    VARCHAR(64)
)
BEGIN

    DECLARE IndexIsThere INTEGER;

    SELECT COUNT(1) INTO IndexIsThere
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_name   = given_table
    AND   index_name   = given_index;

    IF IndexIsThere = 0 THEN
        SELECT CONCAT('Index ', given_index, ' does not exist on table ', given_table) DropindexErrorMessage;
    ELSE
        SET @sqlstmt = CONCAT('DROP INDEX ', given_index, ' ON ', given_table);
        PREPARE st FROM @sqlstmt;
        EXECUTE st;
        DEALLOCATE PREPARE st;
    END IF;

END;

-- create new indexes on some isArchived columns which is used on a lot of queries
CALL CreateIndex('Hashlist', 'isArchived', 'isArchived, hashlistId');
CALL CreateIndex('Task', 'isArchived_priority_taskId', 'isArchived, priority DESC, taskId ASC');

CALL DropIndex('TaskWrapper', 'isArchived'); -- we drop and replace the single isArchived index with the following composite one
CALL CreateIndex('TaskWrapper', 'isArchived_priority_taskWrapperId', 'isArchived, priority DESC, taskWrapperId ASC');
