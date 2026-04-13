<template>
  <section class="panel" style="padding:2rem;max-width:920px;margin:0 auto;">
    <div class="stack">
      <div>
        <div class="muted" style="text-transform:uppercase;letter-spacing:0.18em;font-size:0.8rem;">Reusable foundation</div>
        <h1 style="font-size:clamp(2.5rem,5vw,4.5rem);line-height:0.98;margin:0.4rem 0 0;">Laravel API backend. Nuxt frontend. Modular SaaS starter.</h1>
      </div>

      <p class="muted" style="font-size:1.05rem;max-width:60ch;">
        This starter keeps product assumptions out of the way while giving you authentication, organizations, access control,
        an admin shell, and a clear place for domain modules to grow.
      </p>

      <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
        <NuxtLink class="button" to="/login">Open Auth Flow</NuxtLink>
        <NuxtLink class="button secondary" to="/app">Enter App Shell</NuxtLink>
      </div>
    </div>
  </section>
</template>
