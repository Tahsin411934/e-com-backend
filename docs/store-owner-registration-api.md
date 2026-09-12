# Store owner registration API

The frontend's "Become a partner" form should submit through a server action to
`POST /api/v1/register/store-owner` on the backend. This endpoint is public:
no login, bearer token, session cookie, or CSRF token is required.
The old backend `GET` and `POST /register/store-owner` routes have been removed.

Send `Content-Type: application/json` and `Accept: application/json`.

```json
{
  "first_name": "Rahim",
  "last_name": "Uddin",
  "email": "rahim@example.com",
  "phone": "01711223344",
  "password": "secret1234",
  "password_confirmation": "secret1234",
  "store_name": "Rahim Electronics",
  "currency_code": "BDT",
  "timezone": "Asia/Dhaka"
}
```

| Field | Rules |
| --- | --- |
| `first_name`, `last_name` | Required strings, maximum 255 characters each |
| `email` | Required unique email, maximum 255 characters |
| `password` | Required string, at least 8 characters |
| `password_confirmation` | Must match `password` |
| `store_name` | Required, 2–160 characters; starts with a letter/number, ends with a letter/number, period, or closing parenthesis; allows letters, numbers, spaces and `. , & ( ) ' - / +` |
| `phone` | Optional unique string, maximum 20 characters |
| `store_slug` | Optional unique string, maximum 180 characters, letters/numbers/dashes/underscores; normalized into a URL slug. Generated from store name when omitted |
| `currency_code` | Optional three-letter code, saved uppercase; defaults to `USD` |
| `timezone` | Optional valid timezone identifier; defaults to `UTC` |

No lookup endpoint is needed for this form. Send `BDT` and `Asia/Dhaka` explicitly
if those are the frontend defaults.

Successful registration returns HTTP **201** with `status: "success"`,
`message: "Store owner registration successful."`, and `data` containing
`user` (with roles), `store`, and `token` (a Sanctum bearer token).
The account receives the `Store Owner` role and an active store. Store names
are formatted and generated slugs get a numeric suffix when needed.
The API does not create a backend web login session or mark the email verified.
Keep the token server-side or in a secure HttpOnly cookie if implementing login.

Validation failures return HTTP **422**, including when `Accept` is omitted:

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

Map `errors` to the form fields in the server action. Unexpected registration
failures return HTTP **500** with `status: "error"` and a generic `message`.
Handle network failures separately.

If authenticated store context is needed after registration, use
`GET /api/v1/store` with `Authorization: Bearer <token>`; this endpoint is protected.
