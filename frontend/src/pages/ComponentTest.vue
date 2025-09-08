<template>
  <div class="min-h-screen bg-background p-8">
    <div class="max-w-4xl mx-auto space-y-8">
      <h1 class="text-3xl font-bold text-foreground">UI Components Test</h1>
      
      <!-- Button variants -->
      <VCard>
        <template #header>
          <h2 class="text-xl font-semibold">Button Variants</h2>
        </template>
        <div class="flex flex-wrap gap-4">
          <VButton variant="primary">Primary Button</VButton>
          <VButton variant="secondary">Secondary Button</VButton>
          <VButton variant="ghost">Ghost Button</VButton>
          <VButton variant="destructive">Destructive Button</VButton>
          <VButton variant="outline">Outline Button</VButton>
          <VButton variant="primary" :loading="true">Loading Button</VButton>
          <VButton variant="primary" :disabled="true">Disabled Button</VButton>
        </div>
      </VCard>

      <!-- Button sizes -->
      <VCard>
        <template #header>
          <h2 class="text-xl font-semibold">Button Sizes</h2>
        </template>
        <div class="flex items-center gap-4">
          <VButton size="sm">Small</VButton>
          <VButton size="md">Medium</VButton>
          <VButton size="lg">Large</VButton>
          <VButton size="icon" variant="ghost">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
          </VButton>
        </div>
      </VCard>

      <!-- Input components -->
      <VCard>
        <template #header>
          <h2 class="text-xl font-semibold">Input Components</h2>
        </template>
        <div class="space-y-4 max-w-md">
          <div>
            <VLabel htmlFor="test-input" required>Email Address</VLabel>
            <VInput 
              id="test-input"
              v-model="email"
              type="email"
              placeholder="Enter your email"
              help="We'll never share your email"
            />
          </div>
          <div>
            <VLabel htmlFor="error-input">Input with Error</VLabel>
            <VInput 
              id="error-input"
              v-model="errorInput"
              placeholder="This has an error"
              error="This field is required"
            />
          </div>
          <VCheckbox 
            v-model="agreed"
            label="I agree to the terms and conditions"
            description="By checking this box, you agree to our terms of service."
          />
        </div>
      </VCard>

      <!-- Alerts -->
      <VCard>
        <template #header>
          <h2 class="text-xl font-semibold">Alert Variants</h2>
        </template>
        <div class="space-y-4">
          <VAlert variant="success" title="Success!" message="Your operation completed successfully." />
          <VAlert variant="warning" title="Warning" message="Please review the following information." />
          <VAlert variant="error" title="Error" message="Something went wrong. Please try again." />
          <VAlert variant="info" title="Information" message="Here's some helpful information." />
        </div>
      </VCard>

      <!-- Badges -->
      <VCard>
        <template #header>
          <h2 class="text-xl font-semibold">Badge Variants</h2>
        </template>
        <div class="flex flex-wrap gap-2">
          <VBadge variant="default" text="Default" />
          <VBadge variant="success" text="Success" />
          <VBadge variant="warning" text="Warning" />
          <VBadge variant="destructive" text="Error" />
          <VBadge variant="outline" text="Outline" />
          <VBadge variant="planning" text="Planning" />
          <VBadge variant="active" text="Active" />
          <VBadge variant="completed" text="Completed" />
          <VBadge variant="priority-high" text="High Priority" />
        </div>
      </VCard>

      <!-- Form data display -->
      <VCard>
        <template #header>
          <h2 class="text-xl font-semibold">Form Data</h2>
        </template>
        <pre class="text-sm bg-muted p-4 rounded">{{ { email, errorInput, agreed } }}</pre>
      </VCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { VButton, VInput, VCard, VCheckbox, VLabel, VAlert, VBadge } from '@/components/ui';

const email = ref('');
const errorInput = ref('');
const agreed = ref(false);
</script>