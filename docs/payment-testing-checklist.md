# Payment Integration Testing Checklist

## 1) Stripe Sandbox Validation

- [ ] Configure `.env` with sandbox keys (`STRIPE_PUBLIC_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`)
- [ ] Confirm webhook endpoint is reachable: `/api/payment-webhook.php`
- [ ] Send test webhook events from Stripe CLI/dashboard
- [ ] Validate idempotent event handling (`payment_events.provider_event_id` unique)

## 2) End-to-End Payment Flow

- [ ] Create customer order
- [ ] Approve order in admin panel (status should become `payment_pending`)
- [ ] Open checkout for order and complete payment with sandbox card
- [ ] Verify polling endpoint updates (`/api/payment-status.php?order_id=...`)
- [ ] Confirm order transitions to `payment_confirmed`
- [ ] Confirm invoice row created and viewable at `/invoice.php?order_id=...`
- [ ] Confirm receipt email sent to customer

## 3) Failure & Retry Flow

- [ ] Use sandbox decline card to trigger payment failure
- [ ] Verify customer notification record created in `notifications`
- [ ] Retry payment and confirm status eventually reaches `succeeded`
- [ ] Verify previous failure reason captured in `payments.failure_reason`

## 4) Admin Alerts

- [ ] Execute high-value payment (>= $1000)
- [ ] Confirm admin notifications are generated
- [ ] Trigger suspicious failure reason (fraud-like message in event)
- [ ] Confirm critical admin notification is generated

## 5) Security & Compliance Checks

- [ ] Verify webhook signature validation with `STRIPE_WEBHOOK_SECRET`
- [ ] Confirm no card PAN/CVV stored in database
- [ ] Confirm API endpoints enforce session authorization
- [ ] Ensure only owner can access invoice by `order_id`
- [ ] Check error responses do not leak secrets/stack traces

## 6) Accounting/Reporting Sync

- [ ] Confirm successful payment creates `accounting_sync` record
- [ ] Validate reporting period format (`YYYY-MM`)
- [ ] Simulate/report sync failures and verify status/error recording strategy
