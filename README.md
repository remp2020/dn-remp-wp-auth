# REMP WP Auth plugin

The REMP WP Auth plugin allows you to use [remp/crm-wordpress-module](https://github.com/remp2020/crm-wordpress-module) to authenticate users within REMP CRM with their Wordpress username and password, or with their Wordpress cookie.

## How to Install

### From this repository

Go to the [releases](https://github.com/remp2020/wordpress-tokena-plugin/releases) section of the repository and download the most recent release.

Then, from your WordPress administration panel, go to `Plugins > Add New` and click the `Upload Plugin` button at the top of the page.

## How to Use

From your WordPress administration panel go to `Plugins > Installed Plugins` and scroll down until you find `DN REMP WP Auth` plugin. You will need to activate it first.

### Configuration

#### Authorization token

Plugin requires every call to be authorized by a configured token. Each request validates presence of `Authorization: Bearer token` header. The token found in the header needs to match the token you configure.

To configure the token, add `DN_REMP_WP_AUTH_TOKEN` constant definition into your `wp-config.php` file with the expected value of token. We recommend using random UUIDv4 value.

```php
define( 'DN_REMP_WP_AUTH_TOKEN', 'edc19a6a-5bc6-494f-ba51-9b0731fb0941' );
```

### APIs

Plugin exposes API endpoint to authenticate the user.

#### POST `/api/v1/remp/auth`

##### *Headers:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| Authorization | Bearer *String* | yes | API token. |

##### *Params:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| token | *String* | yes* | Value of WP user cookie. |
| login | *String* | yes* | Login (username) of user in WP. |
| email | *String* | yes* | Email of user in WP. |
| password | *String* | yes* | Password of user in WP. |

One of the following needs to be provided:

* `token`
* `login` + `password`
* `email` + `password`

##### *Example:*

```shell
curl -X POST \
  http://wordpress.press/api/v1/remp/auth \
  -H 'Authorization: Bearer XXX' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'email=admin%40example.com&password=secret'
```

Response:

```json5
{
    "data": {
        "ID": "123",
        "user_login": "admin",
        "user_nicename": "admin",
        "user_email": "admin@example.com",
        "user_url": "",
        "user_registered": "2017-01-01 00:00:00",
        "user_status": "0",
        "display_name": "Example Admin",
        "first_name": "Example",
        "last_name": "Admin"
    },
    "ID": 123,
    "roles": [
        "administrator"
    ]
}
```