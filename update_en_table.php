<?php
// update_en_Table.php
// This program reads the en data from the FCC, parses it
// and updates it in the local en table.
// V2 Updated: 2024-05-16

CREATE FUNCTION parseEnDatFile(content TEXT)
RETURNS TEXT
BEGIN
  DECLARE result TEXT DEFAULT '';
  DECLARE line TEXT DEFAULT '';
  DECLARE pos INT DEFAULT 1;
  
  SET content = CONCAT(content, '\n');
  
  WHILE pos > 0 DO
    SET pos = INSTR(content, '\n');
    SET line = SUBSTRING(content, 1, pos - 1);
    SET content = SUBSTRING(content, pos + 1);
    
    SET result = CONCAT(result,
      SUBSTRING_INDEX(line, '|', 2), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 8), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 9), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 10), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 11), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 12), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 21), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 22), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 23), '|', -1), '\t',
      SUBSTRING_INDEX(SUBSTRING_INDEX(line, '|', 24), '|', -1), '\n'
    );
  END WHILE;
  
  RETURN result;
END
?>