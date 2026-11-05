<template>
  <nav class="pagination" role="navigation" aria-label="pagination">
    <a
      class="pagination-previous"
      :disabled="current <= 1"
      @click="prevPage"
      :style="{ cursor: current <= 1 ? 'not-allowed' : 'pointer' }"
    >←</a>

    <a
      class="pagination-next"
      :disabled="current >= totalPages"
      @click="nextPage"
      :style="{ cursor: current >= totalPages ? 'not-allowed' : 'pointer' }"
    >→</a>

    <ul class="pagination-list">
      <li v-for="page in visiblePages" :key="page">
        <a
          class="pagination-link"
          :class="{ 'is-current': page === current }"
          @click="goToPage(page)"
          :aria-label="`Page ${page}`"
        >{{ page }}</a>
      </li>
    </ul>
  </nav>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'PaginationComponent',
  props: {
    total: {
      type: Number,
      default: 0
    },
    current: {
      type: Number,
      default: 1
    },
    perPage: {
      type: Number,
      default: 20
    }
  },
  emits: ['change'],
  setup(props, { emit }) {
    const totalPages = computed(() => Math.ceil(props.total / props.perPage) || 1);

    const visiblePages = computed(() => {
      const pages = [];
      const rangeStart = Math.max(1, props.current - 2);
      const rangeEnd = Math.min(totalPages.value, props.current + 2);

      for (let i = rangeStart; i <= rangeEnd; i++) {
        pages.push(i);
      }

      return pages;
    });

    const prevPage = () => {
      if (props.current > 1) {
        emit('change', props.current - 1);
      }
    };

    const nextPage = () => {
      if (props.current < totalPages.value) {
        emit('change', props.current + 1);
      }
    };

    const goToPage = (page) => {
      if (page !== props.current && page >= 1 && page <= totalPages.value) {
        emit('change', page);
      }
    };

    return {
      totalPages,
      visiblePages,
      prevPage,
      nextPage,
      goToPage
    };
  }
};
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 4px;
  margin: 10px 0;
}

.pagination-previous,
.pagination-next {
  background-color: var(--color-pagination-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.pagination-previous:hover,
.pagination-next:hover {
  background-color: var(--color-accent);
  color: #fff;
}

.pagination-list {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 4px;
}

.pagination-list li .pagination-link {
  background-color: var(--color-pagination-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  min-width: 32px;
  text-align: center;
  display: inline-block;
}

.pagination-list li .pagination-link:hover {
  background-color: var(--color-accent);
  color: #fff;
}

.pagination-list li .pagination-link.is-current {
  background-color: var(--color-accent);
  color: #fff;
  border-color: var(--color-accent);
}
</style>
