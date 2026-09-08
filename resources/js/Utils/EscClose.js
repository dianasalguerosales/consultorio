import { onMounted, onBeforeUnmount } from 'vue'

export function EscClose(callback) {
  const handleKeydown = (event) => {
    if (event.key === 'Escape') {
      callback()
    }
  }

  onMounted(() => {
    window.addEventListener('keydown', handleKeydown)
  })

  onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown)
  })
}