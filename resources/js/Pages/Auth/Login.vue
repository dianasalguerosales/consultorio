<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Iniciar sesión" />

    <h1 class="text-2xl font-bold text-[#2D2B5B] mb-6">Bienvenido a CAINE</h1>

    <div v-if="status" class="mb-4 font-medium text-sm text-[#74BE69]">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Correo -->
      <div>
        <InputLabel for="email" value="Correo electrónico" class="text-[#2B2B41]" />
        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full border-[#E1DCE2] focus:border-[#53C6D3] focus:ring-[#53C6D3]"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />
        <InputError class="mt-2 text-[#D64550]" :message="form.errors.email" />
      </div>

      <!-- Contraseña -->
      <div>
        <InputLabel for="password" value="Contraseña" class="text-[#2B2B41]" />
        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full border-[#E1DCE2] focus:border-[#53C6D3] focus:ring-[#53C6D3]"
          v-model="form.password"
          required
          autocomplete="current-password"
        />
        <InputError class="mt-2 text-[#D64550]" :message="form.errors.password" />
      </div>

      <!-- Recordarme -->
      <div class="flex items-center">
        <Checkbox name="remember" v-model:checked="form.remember" />
        <span class="ml-2 text-sm text-[#706C7A]">Recordarme</span>
      </div>

      <!-- Acciones -->
      <div class="flex items-center justify-between">
        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="text-sm text-[#53C6D3] hover:text-[#2D2B5B]"
        >
          ¿Olvidaste tu contraseña?
        </Link>

        <PrimaryButton
          class="bg-[#EE518E] hover:bg-[#d64570] px-6 py-2 rounded-md text-white"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Iniciar sesión
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
