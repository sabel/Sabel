CREATE OR REPLACE VIEW autowrite_view AS
  SELECT
    a.id AS adstaff_id,
    a.real_name,
    s.id AS site_id,
    s.name,
    s.url,
    s.class,
    s.encoding,
    (SELECT is_write FROM autowrite AS aw WHERE aw.sites_id=s.id AND aw.adstaff_id=a.id) AS is_write
  FROM sites AS s, adstaff AS a;
