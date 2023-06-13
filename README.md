
# Givecloud Sites

![coverage](https://missioncontrol.givecloud.com/code-coverage/givecloud/givecloud/main/badge.svg)

This is the code that powers Givecloud sites.

## Testing
Documentation for testing is located in [`tests/README.md`](tests/README.md).

### Git Hooks
If you're using Git version 2.9 or greater, set the `core.hooksPath` configuration variable to the managed hooks directory:
```
git config core.hooksPath .githooks
```

### PDF bug with images over HTTPS
The composer package for wkhtmltopdf is using a version that has a bug preventing images from loaded correctly over HTTPS. Until the repo is updated you'll need to download [wkhtmltopdf](https://wkhtmltopdf.org/downloads.html) and install the latest version. Make sure to update the path in your ENV file.

```
wget https://downloads.wkhtmltopdf.org/0.12/0.12.5/wkhtmltox_0.12.5-1.xenial_amd64.deb
dpkg -i wkhtmltox_0.12.5-1.xenial_amd64.deb
```
