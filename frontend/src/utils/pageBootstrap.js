/**
 * pageBootstrap.js — ALLOWED RESPONSIBILITIES (keep this list accurate):
 * - initPageBootstrap: delegates to initMobileNav when #mobileMenuBtn / #mobileMenu exist (shared marketing/catalog-style shells).
 *
 * OUT OF SCOPE (do not add here; use dedicated modules/pages instead):
 * - Form validation, field pairing, feature-specific animations
 * - API calls, auth/session logic, notifications, Stripe, reports, calendar, etc.
 *
 * If you need to add a new bullet under ALLOWED, update this comment in the same commit.
 */
import { initMobileNav } from '../components/layout/mobileNav.js';

export function initPageBootstrap() {
    initMobileNav();
}

export { initDashboardCardAnimations } from './dashboardAnimations.js';
