# WooCommerce plugin

WooCommerce plugin to import orders into the MyParcel.com platform.

## Installation

- Upload the `wc-myparcelcom.zip` file from the [latest release](https://github.com/MyParcelCOM/wc-myparcelcom/releases) via your WordPress Admin
- Connect to your MyParcel.com account via your admin panel: `Settings > MyParcel.com`

## Development

### Update composer while keeping PHP 8.0 polyfills

```shell
docker run -it --rm -v "$PWD":/usr/src/app -w /usr/src/app php:8.0-apache composer update
```

### Create .zip file for release

```shell
git clone git@github.com:MyParcelCOM/wc-myparcelcom.git
rm -rf wc-myparcelcom/.git
docker run -it --rm -v "$PWD"/wc-myparcelcom:/usr/src/app -w /usr/src/app php:8.0-apache composer install
zip -vr wc-myparcelcom-v3.0.0.zip wc-myparcelcom
```
