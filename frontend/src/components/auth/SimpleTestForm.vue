<template>
  <div class="p-8 bg-white">
    <h2 class="text-2xl font-bold mb-4">Simple Form Test</h2>
    
    <!-- Basic Input Test -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">Test Input (ref)</label>
      <input 
        v-model="testRef"
        type="text" 
        placeholder="Type here..."
        style="width: 100%; padding: 8px; border: 2px solid #ccc; border-radius: 4px;"
      />
      <p style="margin-top: 8px; color: blue;">Value: "{{ testRef }}"</p>
    </div>

    <!-- Reactive Object Test -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">Form Object Test (reactive)</label>
      <input 
        v-model="formData.name"
        type="text" 
        placeholder="Type here..."
        style="width: 100%; padding: 8px; border: 2px solid #ccc; border-radius: 4px;"
      />
      <p style="margin-top: 8px; color: green;">Name: "{{ formData.name }}"</p>
      <p style="color: green;">Full object: {{ JSON.stringify(formData) }}</p>
    </div>

    <!-- Event Handler Test -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">Event Handler Test</label>
      <input 
        type="text" 
        placeholder="Type here..."
        style="width: 100%; padding: 8px; border: 2px solid #ccc; border-radius: 4px;"
        @input="handleInput"
      />
      <p style="margin-top: 8px; color: red;">Last input: "{{ lastInput }}"</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'

const testRef = ref('')
const formData = reactive({
  name: '',
  email: ''
})
const lastInput = ref('')

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  lastInput.value = target.value
  console.log('Input event fired:', target.value)
}
</script>