CREATE OR REPLACE VIEW characters_view AS
  SELECT
    c.*,
    l.omit_name,
    l.name AS location_name,
    l.area_name AS area_name,
    i.name AS invitation_name,
    f.name AS freetimes_name
  FROM characters AS c
  INNER JOIN location AS l ON (c.location_id = l.id)
  INNER JOIN invitation AS i ON (c.invitation_id = i.id)
  INNER JOIN freetimes AS f ON (c.freetimes_id = f.id);
