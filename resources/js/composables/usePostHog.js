import posthog from 'posthog-js'

let initialized = false

export function usePostHog() {
  if (!initialized) {
    const apiKey = import.meta.env.VITE_POSTHOG_KEY || 'phc_rbtKPZwd03xF77hZPKiWHLoShG7ewlp56m5F8olRnWB'
    const apiHost = import.meta.env.VITE_POSTHOG_HOST || 'https://us.i.posthog.com'

    posthog.init(apiKey, {
      api_host: apiHost,
      defaults: '2025-05-24',
      person_profiles: 'identified_only',
      capture_pageview: true,
      capture_pageleave: true,
    })

    initialized = true
  }

  return { posthog }
}
