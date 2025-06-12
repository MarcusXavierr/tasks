import { vi } from 'vitest'

// Mock Inertia
declare global {
  var route: any
}

globalThis.route = vi.fn()

// Mock the Head component from Inertia
vi.mock('@inertiajs/vue3', () => ({
  Head: {
    name: 'Head',
    template: '<head><title>{{ title }}</title></head>',
    props: ['title']
  }
})) 