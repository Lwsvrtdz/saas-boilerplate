<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: ['auth'],
})

const auth = useAuth()
</script>

<template>
  <section class="stack">
    <div class="panel" style="padding:1.5rem;">
      <div class="muted" style="text-transform:uppercase;letter-spacing:0.16em;font-size:0.8rem;">Workspace</div>
      <h1 style="margin:0.35rem 0 0;">Application shell</h1>
      <p class="muted">
        Start product work here. The shell is intentionally generic so each new SaaS can define its own navigation,
        dashboards, and modules without fighting inherited branding.
      </p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
      <div class="panel" style="padding:1.25rem;">
        <div class="muted">Signed in as</div>
        <div style="font-weight:700;font-size:1.15rem;">{{ auth.user.value?.name }}</div>
      </div>
      <div class="panel" style="padding:1.25rem;">
        <div class="muted">Current organization</div>
        <div style="font-weight:700;font-size:1.15rem;">{{ auth.currentOrganization.value?.name ?? 'Not selected' }}</div>
      </div>
    </div>
  </section>
</template>
