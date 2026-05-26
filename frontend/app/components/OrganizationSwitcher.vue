<script setup lang="ts">
const auth = useAuth()
const selectedOrganizationId = computed({
  get: () => auth.currentOrganization.value?.id ?? '',
  set: async (value) => {
    const organization = auth.organizations.value.find((item) => item.id === Number(value))

    if (organization) {
      await auth.switchOrganization(organization)
    }
  },
})

onMounted(async () => {
  if (auth.token.value && auth.organizations.value.length === 0) {
    await auth.fetchOrganizations()
  }
})
</script>

<template>
  <label class="field">
    <span>Organization</span>
    <select v-model="selectedOrganizationId">
      <option
        v-for="organization in auth.organizations.value"
        :key="organization.id"
        :value="organization.id"
      >
        {{ organization.name }}
      </option>
    </select>
  </label>
</template>
