-- Cracker binaries can be hosted on the server itself: 'filename' stores the archive
-- filename of the locally stored .7z archive, NULL means the binary is downloaded from downloadUrl.
ALTER TABLE CrackerBinary ADD COLUMN filename VARCHAR(100) NULL AFTER binaryName;

-- downloadUrl can now point to the hashtopolis server download endpoint, so more space is needed
ALTER TABLE CrackerBinary MODIFY downloadUrl VARCHAR(255) NOT NULL;
