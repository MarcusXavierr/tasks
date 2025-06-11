import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import Dashboard from '@/Pages/Dashboard.vue'

interface Task {
    id: number;
    title: string;
    status: 'pending' | 'in-progress' | 'completed';
    priority: 'low' | 'medium' | 'high';
    due_date: string;
}

// Mock AuthenticatedLayout
vi.mock('@/Layouts/AuthenticatedLayout.vue', () => ({
  default: {
    name: 'AuthenticatedLayout',
    template: `
      <div class="authenticated-layout">
        <header><slot name="header"></slot></header>
        <main><slot></slot></main>
      </div>
    `
  }
}))

// Mock Head component
vi.mock('@inertiajs/vue3', () => ({
  Head: {
    name: 'Head',
    template: '<head><title>{{ title }}</title></head>',
    props: ['title']
  }
}))

describe('Dashboard Component', () => {
  const mockTasks: Task[] = [
    {
      id: 1,
      title: 'Test Task 1',
      status: 'pending',
      priority: 'high',
      due_date: '2024-01-15'
    },
    {
      id: 2,
      title: 'Test Task 2',
      status: 'in-progress',
      priority: 'medium',
      due_date: '2024-01-20'
    },
    {
      id: 3,
      title: 'Test Task 3',
      status: 'completed',
      priority: 'low',
      due_date: '2024-01-25'
    }
  ]

  it('renders correctly with no tasks', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: []
      }
    })

    expect(wrapper.find('h2').text()).toBe('Tasks Dashboard')
    expect(wrapper.text()).toContain('No tasks found. Create some tasks to get started!')
    expect(wrapper.find('table').exists()).toBe(false)
  })

  it('renders correctly with tasks', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: mockTasks
      }
    })

    expect(wrapper.find('h2').text()).toBe('Tasks Dashboard')
    expect(wrapper.find('table').exists()).toBe(true)
    expect(wrapper.findAll('tbody tr')).toHaveLength(3)
  })

  it('displays task information correctly', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: [mockTasks[0]]
      }
    })

    const row = wrapper.find('tbody tr')
    expect(row.text()).toContain('Test Task 1')
    expect(row.text()).toContain('Pending')
    expect(row.text()).toContain('High')
    expect(row.text()).toContain('1/15/2024')
  })

  it('applies correct status badge classes', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: mockTasks
      }
    })

    const rows = wrapper.findAll('tbody tr')
    
    // Check pending status
    expect(rows[0].find('.bg-gray-100.text-gray-800').text()).toBe('Pending')
    
    // Check in-progress status
    expect(rows[1].find('.bg-yellow-100.text-yellow-800').text()).toBe('In progress')
    
    // Check completed status
    expect(rows[2].find('.bg-green-100.text-green-800').text()).toBe('Completed')
  })

  it('applies correct priority badge classes', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: mockTasks
      }
    })

    const rows = wrapper.findAll('tbody tr')
    
    // Check high priority (first task)
    expect(rows[0].find('.bg-red-100.text-red-800').text()).toBe('High')
    
    // Check medium priority (second task)
    expect(rows[1].find('.bg-blue-100.text-blue-800').text()).toBe('Medium')
    
    // Check low priority (third task) - this was the failing test
    // The third task has 'completed' status which uses green background, 
    // but 'low' priority should also use green background
    // We need to find the priority badge specifically, not the status badge
    const priorityBadges = rows[2].findAll('.bg-green-100.text-green-800')
    // There should be 2 green badges: one for 'completed' status and one for 'low' priority
    expect(priorityBadges).toHaveLength(2)
    // The second green badge should be the priority badge
    expect(priorityBadges[1].text()).toBe('Low')
  })

  it('formats dates correctly', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: [{
          id: 1,
          title: 'Test Task',
          status: 'pending',
          priority: 'high',
          due_date: '2024-12-25'
        }]
      }
    })

    expect(wrapper.text()).toContain('12/25/2024')
  })

  it('handles undefined tasks prop gracefully', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: undefined as any
      }
    })

    expect(wrapper.text()).toContain('No tasks found. Create some tasks to get started!')
  })

  it('shows table headers correctly', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: mockTasks
      }
    })

    const headers = wrapper.findAll('th')
    expect(headers).toHaveLength(4)
    expect(headers[0].text()).toBe('Title')
    expect(headers[1].text()).toBe('Status')
    expect(headers[2].text()).toBe('Priority')
    expect(headers[3].text()).toBe('Due Date')
  })

  it('applies hover effect on table rows', () => {
    const wrapper = mount(Dashboard, {
      props: {
        tasks: mockTasks
      }
    })

    const rows = wrapper.findAll('tbody tr')
    rows.forEach(row => {
      expect(row.classes()).toContain('hover:bg-gray-50')
    })
  })
}) 