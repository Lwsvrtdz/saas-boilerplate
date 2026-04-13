<script setup lang="ts">
const auth = useAuth()

const links = [
  { label: 'Overview', to: '/app' },
  { label: 'Admin', to: '/admin' },
]
</script>

<template>
  <div class="panel" style="display:grid;grid-template-columns:240px 1fr;overflow:hidden;">
    <aside style="padding:1.25rem;border-right:1px solid var(--border);display:grid;gap:1rem;background:rgba(255,255,255,0.6);">
      <div>
        <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.16em;" class="muted">Starter</div>
        <div style="font-size:1.4rem;font-weight:700;">{{ auth.appName }}</div>
      </div>

      <nav style="display:grid;gap:0.5rem;">
        <NuxtLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="panel"
          style="padding:0.9rem 1rem;box-shadow:none;"
        >
          {{ link.label }}
        </NuxtLink>
      </nav>

      <div class="panel" style="padding:1rem;box-shadow:none;">
        <div style="font-weight:600;">{{ auth.user.value?.name ?? 'Guest' }}</div>
        <div class="muted" style="font-size:0.9rem;">{{ auth.user.value?.email ?? 'Not authenticated' }}</div>
        <button class="button secondary" style="margin-top:0.9rem;width:100%;" @click="auth.logout">
          Sign out
        </button>
      </div>
    </aside>

    <main style="padding:1.5rem;">
      <slot />
    </main>
  </div>
</template>
