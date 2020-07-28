## Requires preliminary created:
- MySQL database named "csvdata";
- MySQL user with access to this DB;
- Password to this user encripted with base64 and saved to the file /opt/tp.txt i.e.:
```
echo 'password'|head -c -1|base64 >/opt/tp.txt
```

## Testing:
####  upload data_ini.csv file (should reflect 4 rows added)
####  upload data_upd.csv file  (should reflect 3 by 1 rows added, deleted and updated)
