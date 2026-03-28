# Backend Refactor Completion Checklist

Use this checklist before moving fully into feature implementation.

## 1) Routing parity

- [x] Local dev uses router script:
  - `php -S 127.0.0.1:8000 dev-router.php`
- [x] Apache equivalent rewrite rules are enabled from `.htaccess`
- [x] Nginx equivalent rewrite rules are enabled from `docs/nginx-routing.conf`
- [x] `/` loads `public/pages/index.php`
- [x] Legacy page URLs like `/dashboard.php` and `/checkout.php` resolve to `public/pages/*`
- [x] `/api.php?endpoint=...` remains directly reachable

## 2) Domain tests

From `src/`:

- [x] `php tests/reset_test_db.php`
- [x] Queue tests pass
- [x] User tests pass
- [x] Order tests pass
- [x] Sample tests pass
- [x] Payment tests pass

## 3) Payment critical path

- [ ] Create order transitions to `PAYMENT_PENDING` without SQL truncation warnings
- [ ] Checkout creates payment intent successfully
- [ ] Redirect return updates payment state (or polling refresh reflects provider state)
- [ ] Webhook endpoint receives and processes provider events (`api.php?endpoint=payment-webhook`)
- [ ] Final order status moves to paid/processing path as expected

## 4) Frontend smoke checks

- [ ] Login/logout flow works
- [x] Dashboard route protection works (unauthenticated `/dashboard.php` redirects to login)
- [ ] Admin approvals tab functions
- [ ] Order creation + checkout path works end-to-end
- [ ] Main CSS/JS assets load without 404s

## 5) Cleanup and guardrails

- [ ] No runtime references remain to removed legacy `api/*.php` endpoints (excluding `docs/deprecated-api`)
- [x] `public/pages` is the only home for page scripts
- [x] Docs/readme reflect new run commands and routing model
- [ ] Commit includes migration notes for deployment routing

