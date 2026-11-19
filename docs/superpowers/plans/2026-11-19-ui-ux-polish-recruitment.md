# Recruitment UI/UX Polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Polish UI/UX of Recruitment module (`Index.vue`, `Users.vue`, `Onboarding.vue`) to meet high aesthetic and accessibility standards (WCAG 2.1, clean typography, proper feedback states, consistent design tokens).

**Architecture:** Refine Vue components using existing Tailwind tokens (`bg-surface`, `text-main`, `text-muted`, `border-subtle`, `bg-accent`), ensure keyboard accessibility, enhance form error/loading states, and ensure proper spacing.

**Tech Stack:** Vue 3, Inertia.js, Tailwind CSS, TypeScript

**Spec:** PRD.md / SDD.md / Web Interface Guidelines / Design Taste Frontend

## Global Constraints

- Strict adherence to Laravel/Vue conventions in the project.
- Use existing UI components (`AppButton`, `Card`, `StatusBadge`).
- Ensure no broken routes or missing props.
- Maintain responsive layout across all breakpoints.

## Review Focus

1. Modal accessibility (focus trapping, ESC key closing).
2. Form submission loading feedback on buttons.
3. Empty states readability and contrast.
4. Pagination styling consistency.
5. Mobile layout stacking and touch targets (min 44px).

---

### Task 1: Polish Recruitment Index Page (`Index.vue`)

**Files:**
- Modify: `resources/js/Pages/Recruitment/Index.vue`

**Interfaces:**
- Consumes: Existing Inertia props (`vacancies`, `applicants`, `isAdminOrHead`, `scopeEmployeeId`)
- Produces: Polished Recruitment Index page with enhanced card layouts, better empty states, accessible modal, and smooth transition states.

- [ ] **Step 1: Enhance vacancy cards with better spacing and hover states**

Update `resources/js/Pages/Recruitment/Index.vue` to refine card styling, ensuring proper border contrast (`border-subtle hover:border-accent`), clear typography hierarchy, and distinct badge placement.

- [ ] **Step 2: Improve Create Vacancy Modal accessibility and form validation UX**

Add proper labels, clear focus rings, and explicit loading states on submit buttons in the modal.

- [ ] **Step 3: Run build and verify no syntax or compilation errors**

Run: `npm run build`
Expected: Success with no errors.

- [ ] **Step 4: Commit changes**

```bash
git add resources/js/Pages/Recruitment/Index.vue
git commit -m "refactor(ui): polish recruitment index page layout and accessibility"
```

---

### Task 2: Polish Recruitment Users Page (`Users.vue`)

**Files:**
- Modify: `resources/js/Pages/Recruitment/Users.vue`

**Interfaces:**
- Consumes: Inertia props (`users`)
- Produces: Polished User management list with refined avatar placeholders, clear role/department badges, and accessible creation modal.

- [ ] **Step 1: Refine user list item layout and styling**

Update `resources/js/Pages/Recruitment/Users.vue` to use cohesive spacing, clear typography, and clean status badges.

- [ ] **Step 2: Improve Add User Modal fields and loading feedback**

Ensure form inputs have clear labels, proper dark/light canvas colors, and disabled states during submission.

- [ ] **Step 3: Run build check**

Run: `npm run build`
Expected: Success.

- [ ] **Step 4: Commit changes**

```bash
git add resources/js/Pages/Recruitment/Users.vue
git commit -m "refactor(ui): polish recruitment users page and creation modal"
```

---

### Task 3: Polish Onboarding Page (`Onboarding.vue`)

**Files:**
- Modify: `resources/js/Pages/Recruitment/Onboarding.vue`

**Interfaces:**
- Consumes: Inertia props for onboarding tasks/status.
- Produces: Clean, well-structured onboarding workflow view.

- [ ] **Step 1: Check and refine Onboarding view layout**

Read `resources/js/Pages/Recruitment/Onboarding.vue` and apply identical design tokens and spacing.

- [ ] **Step 2: Run build and tests**

Run: `npm run build && php artisan test`
Expected: All tests pass, build succeeds.

- [ ] **Step 3: Commit changes**

```bash
git add resources/js/Pages/Recruitment/Onboarding.vue
git commit -m "refactor(ui): polish recruitment onboarding page"
```
