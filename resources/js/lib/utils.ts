import { defineComponent, h } from 'vue';

export const RouterLink = defineComponent({
  name: 'RouterLink',
  props: ['to'],
  setup(props, { slots }) {
    return () => h('a', { href: props.to }, slots.default ? slots.default() : []);
  },
});
