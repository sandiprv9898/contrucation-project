<template>
  <div class="user-form p-4">
    <h1 class="text-2xl font-bold mb-4">TEST: Create New User Form</h1>
    <div class="border border-gray-300 p-4 rounded">
      <p class="mb-4">This is a test to verify the component is rendering.</p>
      
      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input 
            type="text" 
            class="w-full border border-gray-300 rounded px-3 py-2"
            placeholder="Enter your name"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input 
            type="email" 
            class="w-full border border-gray-300 rounded px-3 py-2"
            placeholder="Enter your email"
          />
        </div>
        
        <div class="flex gap-3">
          <button 
            type="button" 
            class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Create User
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
// Minimal test script
console.log('UserForm component is loading...');

// Define minimal props to keep the interface working
interface Props {
  user?: any;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  user: null,
  loading: false
});

const emit = defineEmits<{
  submit: [data: any];
  cancel: [];
}>();
</script>