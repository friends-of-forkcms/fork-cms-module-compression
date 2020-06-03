-- Execute these queries to uninstall the module (used for development)

-- Drop module tables
DROP TABLE IF EXISTS CompressionHistory;
DROP TABLE IF EXISTS CompressionSetting;

-- Remove from backend navigation
DELETE FROM backend_navigation WHERE label = 'Compression';
DELETE FROM backend_navigation WHERE url = '%compression%';

-- Remove from groups_rights
DELETE FROM groups_rights_actions WHERE module = 'Compression';
DELETE FROM groups_rights_modules WHERE module = 'Compression';

-- Remove from locale
DELETE FROM locale WHERE module = 'Compression';
DELETE FROM locale WHERE module = 'core' AND name LIKE 'Compression%';

-- Remove from modules
DELETE FROM modules WHERE name = 'Compression';
DELETE FROM modules_extras WHERE module = 'Compression';
DELETE FROM modules_settings WHERE module = 'Compression';
