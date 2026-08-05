# Security Policy

## Supported versions

| Version | Supported |
|---|---|
| 1.0.x | Yes |
| < 1.0 | No |

This project follows the `main` branch. Fixes land there first and are released as a patch version.

## Reporting a vulnerability

**Please do not open a public issue for a security problem.**

Report it privately in one of these ways:

1. **GitHub private vulnerability reporting** — the *Security* tab → *Report a vulnerability*. This is preferred.
2. **Email** — `info@iskills.com`

Please include what you found, how to reproduce it, the version or commit, and the impact you think it has. A working proof of concept helps a lot.

What to expect: an acknowledgement within a few days, an assessment and a fix timeline after that, and credit in the release notes if you would like it. Please give a reasonable window to ship a fix before disclosing publicly.

## Things you should know before deploying

These are known, deliberate risk surfaces. They are documented rather than hidden, but you need to understand them.

### The `/_ops/{action}` maintenance route is high-risk

Portfolio OS targets shared hosting where SSH is often unavailable, so it ships a route that runs Artisan commands over HTTP:

```
GET /_ops/{migrate|storage-link|cache-clear|optimize|livewire-assets}?token=…
```

Anyone with the token can run migrations and clear caches on your installation.

- It is **disabled entirely (404) when `OPS_TOKEN` is empty.** That is the default in `.env.example`, and it should be the steady state.
- Set `OPS_TOKEN` to 32+ random characters only for as long as a deploy needs it, then **clear or rotate it** and clear the config cache.
- Only ever use it over **HTTPS**. Over plain HTTP the token is in the URL, in the clear, and in access logs.
- It is rate-limited per IP (`OPS_THROTTLE`, default 5/minute), which slows guessing but is not a substitute for a strong token.
- Never commit a real token, and never leave one in a config cache you ship.

If your host gives you SSH or a terminal, you do not need this route at all — leave `OPS_TOKEN` empty permanently.

### Credential vault encryption model

Project credentials (hosting, CMS, registrar, ad network logins) are stored encrypted at rest using Laravel's encrypter, which derives from **`APP_KEY`**. Consequences worth internalising:

- **`APP_KEY` is the only thing protecting the vault.** Treat it like the secrets it protects: never commit it, never reuse a key from an example file or a demo, generate a fresh one per installation with `php artisan key:generate`.
- **Rotating `APP_KEY` makes every existing vault row permanently unreadable.** There is no re-encryption command. Export what you need first.
- A database dump alone does not expose secrets, but a database dump *plus* your `.env` does. Store backups accordingly.
- Every reveal of a secret is written to an audit log with the user, timestamp and IP. Reveals are a permission (`credentials.reveal`) separate from viewing that a credential exists.

### AI provider boundary

When the optional AI assistant is configured, credential rows, passwords, API keys and bank details are stripped from the payload before any prompt is built, and the model is never allowed to generate SQL that gets executed — questions map onto a fixed whitelist of read-only report methods that re-apply the caller's own permissions.

With no `AI_API_KEY` set, no outbound call is ever made. If you would rather not trust the boundary, leave it unset.

### Other notes

- Financial records are soft-deleted, never hard-deleted, and approved distribution runs are immutable. This is an integrity control, not just a convenience.
- `APP_DEBUG=false` in production, always. Laravel's debug page discloses environment variables.
- The demo seeders create accounts with a known password when `APP_ENV=local`. Never run them on a real installation.
- Uploaded receipts and attachments live on the private disk by default. Only files under `storage/app/public` are web-reachable.
