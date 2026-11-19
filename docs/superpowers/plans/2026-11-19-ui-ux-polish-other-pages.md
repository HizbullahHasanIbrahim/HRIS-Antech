# Other Pages UI/UX Polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Polish Dashboard, Career, Schedules, Welfare, and Reports pages to match the refined Recruitment UI quality with improved visual hierarchy, consistent spacing, and accessibility enhancements.

**Architecture:** Apply design token improvements consistently across remaining pages, ensuring card layouts, empty states, modal dialogs, and data tables follow the same pattern established in Recruitment pages.

**Tech Stack:** Vue 3, Inertia.js, Tailwind CSS, TypeScript

**Spec:** PRD.md / SDD.md / Web Interface Guidelines / Design Taste Frontend

## Global Constraints

- Maintain existing data structures and API contracts
- Use existing UI components (`AppButton`, `Card`, `StatusBadge`, `ApexChart`)
- Ensure all interactive elements are keyboard accessible
- Ensure responsive layout across all breakpoints
- No breaking changes to route handlers

## Review Focus

1. Modal accessibility (focus trapping, ESC closing)
2. Empty state clarity and helpful messaging
3. Card layout consistency with Recruitment pages
4. Color contrast for status indicators
5. Proper aria-labels on icons and buttons

---

### Task 1: Polish Dashboards Page (`Dashboard/Index.vue`)

**Files:**
- Modify: `resources/js/Pages/Dashboard/Index.vue`

**Interfaces:**
- Consumes: `stats` prop (all numeric metrics)
- Produces: Polished dashboard with consistent stat cards, refined activity feed, improved heading hierarchy

- [ ] **Step 1: Refactor stat cards with consistent sizing and hover effects**
- [ ] **Step 2: Improve activity feed indicator styling**
- [ ] **Step 3: Build and verify**
- [ ] **Step 4: Commit**

---

### Task 2: Polish Career Pages (`Career/Index.vue`, `Certifications.vue`, `StudyLeave.vue`, `Training.vue`)

**Files:**
- Modify: `resources/js/Pages/Career/Index.vue`
- Modify: `resources/js/Pages/Career/Certifications.vue`
- Modify: `resources/js/Pages/Career/StudyLeave.vue`
- Modify: `resources/js/Pages/Career/Training.vue`

**Interfaces:**
- Produces: Consistent Career section layout with unified card styles

- [ ] **Step 1: Review and polish Career pages**
- [ ] **Step 2: Build and verify**
- [ ] **Step 3: Commit**

---

### Task 3: Polish Schedules Page (`Schedules/Index.vue`)

**Files:**
- Modify: `resources/js/Pages/Schedules/Index.vue`

**Interfaces:**
- Uses: `schedules` prop
- Produces: Clear schedule cards with day grid, accessible modal

- [ ] **Step 1: Refactor schedule cards with hover effects**
- [ ] **Step 2: Improve empty state**
- [ ] **Step 3: Build and verify**
- [ ] **Step 4: Commit**

---

### Task 4: Polish Welfare Page (`Welfare/Bpjs.vue`)

**Files:**
- Modify: `resources/js/Pages/Welfare/Bpjs.vue`

**Interfaces:**
- Uses: `memberships` prop
- Produces: Clean BPJS membership list with masked number display, accessible modal

- [ ] **Step 1: Refactor BPJS membership cards**
- [ ] **Step 2: Improve pagination accessibility**
- [ ] **Step 3: Build and verify**
- [ ] **Step 4: Commit**

---

### Task 5: Polish Reports Page (`Reports/Index.vue`)

**Files:**
- Modify: `resources/js/Pages/Reports/Index.vue`

**Interfaces:**
- Uses: `kpi`, `charts`, `compliance`, `units`, `filters`, `exportUrls` props
- Produces: Enhanced charts with skeleton states, improved tab navigation

- [ ] **Step 1: Add chart loading skeletons**
- [ ] **Step 2: Improve tab accessibility**
- [ ] **Step 3: Build and verify**
- [ ] **Step 4: Commit**
