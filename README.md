# morph-api
This is a project of Morph Backend APIs.

## Jobs

- Push uploaded files to queue
```
*/30 * * * * root /{root path}/webroot/yii jobs/file-process > /dev/null 2>&1
```

- Export all files
```
0 * * * * root /{root path}/webroot/yii feed-fie/update-all > /dev/null 2>&1
```
