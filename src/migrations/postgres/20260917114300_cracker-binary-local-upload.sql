-- Cracker binaries can be hosted on the server itself: 'filename' stores the archive
-- filename of the locally stored .7z archive, NULL means the binary is downloaded from downloadUrl.
ALTER TABLE CrackerBinary ADD COLUMN filename TEXT NULL;
