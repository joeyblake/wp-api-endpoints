# wp-api-endpoints

This abstract class provides a base for setting up ajax endpoints in WordPress using rewrite endpoints instead of admin-ajax requests.

There are a number of benefits to this approach.

1. A unique url that is server cachable for high traffic, reader facing endpoints such as infinite scroll.
2. Allows organization of api endpoints into a use specific class with a unique endpoint. For better code organization.
3. Makes api building and testing easier because you can request the url directly in the browser.

Check the implementation example to get started.
