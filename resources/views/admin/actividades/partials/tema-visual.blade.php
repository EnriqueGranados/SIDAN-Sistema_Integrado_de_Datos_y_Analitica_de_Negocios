@once
    <style>
        .activity-ui {
            --activity-surface: #ffffff;
            --activity-surface-soft: #f8fafc;
            --activity-surface-muted: #f1f5f9;
            --activity-border: #e2e8f0;
            --activity-border-strong: #cbd5e1;
            --activity-text: #0f172a;
            --activity-text-soft: #334155;
            --activity-text-muted: #64748b;
            --activity-shadow: 0 1px 2px rgb(15 23 42 / 0.04), 0 10px 28px rgb(15 23 42 / 0.06);
            --activity-accent: #10b981;
            --activity-accent-soft: rgb(16 185 129 / 0.10);
            --activity-accent-ring: rgb(16 185 129 / 0.18);
            --activity-info: #06b6d4;
            --activity-info-soft: rgb(6 182 212 / 0.10);
        }

        .dark .activity-ui {
            --activity-surface: #0f172a;
            --activity-surface-soft: rgba(255, 255, 255, 0.03);
            --activity-surface-muted: rgba(255, 255, 255, 0.05);
            --activity-border: rgba(255, 255, 255, 0.10);
            --activity-border-strong: rgba(255, 255, 255, 0.15);
            --activity-text: #ffffff;
            --activity-text-soft: #d1d5db;
            --activity-text-muted: #9ca3af;
            --activity-shadow: none;
            --activity-accent-soft: rgb(16 185 129 / 0.14);
            --activity-accent-ring: rgb(16 185 129 / 0.30);
            --activity-info-soft: rgb(6 182 212 / 0.14);
        }

        .activity-ui [class~="text-white"] {
            color: var(--activity-text) !important;
        }

        .activity-ui [class~="text-gray-300"],
        .activity-ui [class~="text-slate-300"] {
            color: var(--activity-text-soft) !important;
        }

        .activity-ui [class~="text-gray-400"],
        .activity-ui [class~="text-slate-400"] {
            color: #475569 !important;
        }

        .activity-ui [class~="text-gray-500"],
        .activity-ui [class~="text-slate-500"] {
            color: #64748b !important;
        }

        .activity-ui [class~="text-gray-600"],
        .activity-ui [class~="text-slate-600"] {
            color: #94a3b8 !important;
        }

        .dark .activity-ui [class~="text-gray-400"],
        .dark .activity-ui [class~="text-slate-400"] {
            color: #9ca3af !important;
        }

        .dark .activity-ui [class~="text-gray-500"],
        .dark .activity-ui [class~="text-slate-500"] {
            color: #6b7280 !important;
        }

        .dark .activity-ui [class~="text-gray-600"],
        .dark .activity-ui [class~="text-slate-600"] {
            color: #4b5563 !important;
        }

        .activity-ui [class~="border-white/10"],
        .activity-ui [class~="border-slate-700"],
        .activity-ui [class~="border-gray-700"] {
            border-color: var(--activity-border) !important;
        }

        .activity-ui [class~="border-white/15"] {
            border-color: var(--activity-border-strong) !important;
        }

        /* Separadores internos de tablas y listados.
           Tailwind divide-white/5 casi desaparece en modo claro, así que lo adaptamos al tema. */
        .activity-ui .divide-white\/5> :not([hidden])~ :not([hidden]),
        .activity-ui .divide-white\/10> :not([hidden])~ :not([hidden]) {
            border-color: #e2e8f0 !important;
        }

        .dark .activity-ui .divide-white\/5> :not([hidden])~ :not([hidden]) {
            border-color: rgba(255, 255, 255, 0.07) !important;
        }

        .dark .activity-ui .divide-white\/10> :not([hidden])~ :not([hidden]) {
            border-color: rgba(255, 255, 255, 0.10) !important;
        }

        .dark .activity-ui [class~="border-slate-700"] {
            border-color: #334155 !important;
        }

        .dark .activity-ui [class~="border-gray-700"] {
            border-color: #374151 !important;
        }

        .activity-ui [class~="bg-white/[0.03]"],
        .activity-ui [class~="bg-white/[0.04]"],
        .activity-ui [class~="bg-white/[0.05]"],
        .activity-ui [class~="bg-black/10"],
        .activity-ui [class~="bg-black/20"],
        .activity-ui [class~="bg-slate-900"],
        .activity-ui [class~="bg-gray-900"],
        .activity-ui [class~="bg-[#111827]"],
        .activity-ui [class~="bg-[#10151f]"] {
            background-color: var(--activity-surface) !important;
        }

        .activity-ui [class~="bg-white/5"],
        .activity-ui [class~="bg-white/[0.06]"],
        .activity-ui [class~="bg-slate-900/50"],
        .activity-ui [class~="bg-gray-800"] {
            background-color: var(--activity-surface-muted) !important;
        }

        .dark .activity-ui [class~="bg-white/[0.03]"] {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        .dark .activity-ui [class~="bg-white/[0.04]"] {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        .dark .activity-ui [class~="bg-white/[0.05]"] {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .dark .activity-ui [class~="bg-white/[0.06]"] {
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        .dark .activity-ui [class~="bg-white/5"] {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .dark .activity-ui [class~="bg-black/10"] {
            background-color: rgba(0, 0, 0, 0.10) !important;
        }

        .dark .activity-ui [class~="bg-black/20"] {
            background-color: rgba(0, 0, 0, 0.20) !important;
        }

        .dark .activity-ui [class~="bg-slate-900"] {
            background-color: #0f172a !important;
        }

        .dark .activity-ui [class~="bg-slate-900/50"] {
            background-color: rgb(15 23 42 / 0.50) !important;
        }

        .dark .activity-ui [class~="bg-gray-900"] {
            background-color: #111827 !important;
        }

        .dark .activity-ui [class~="bg-gray-800"] {
            background-color: #1f2937 !important;
        }

        .dark .activity-ui [class~="bg-[#111827]"] {
            background-color: #111827 !important;
        }

        .dark .activity-ui [class~="bg-[#10151f]"] {
            background-color: #10151f !important;
        }

        .activity-ui [class~="hover:bg-white/5"]:hover,
        .activity-ui [class~="hover:bg-white/[0.03]"]:hover,
        .activity-ui [class~="hover:bg-white/[0.04]"]:hover,
        .activity-ui [class~="hover:bg-white/[0.06]"]:hover,
        .activity-ui [class~="hover:bg-white/10"]:hover {
            background-color: #f1f5f9 !important;
        }

        .dark .activity-ui [class~="hover:bg-white/5"]:hover {
            background-color: rgba(255, 255, 255, .05) !important;
        }

        .dark .activity-ui [class~="hover:bg-white/[0.03]"]:hover {
            background-color: rgba(255, 255, 255, .03) !important;
        }

        .dark .activity-ui [class~="hover:bg-white/[0.04]"]:hover {
            background-color: rgba(255, 255, 255, .04) !important;
        }

        .dark .activity-ui [class~="hover:bg-white/[0.06]"]:hover {
            background-color: rgba(255, 255, 255, .06) !important;
        }

        .dark .activity-ui [class~="hover:bg-white/10"]:hover {
            background-color: rgba(255, 255, 255, .10) !important;
        }

        .activity-ui [class~="hover:text-white"]:hover {
            color: #0f172a !important;
        }

        .dark .activity-ui [class~="hover:text-white"]:hover {
            color: #ffffff !important;
        }

        .activity-ui input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        .activity-ui select,
        .activity-ui textarea {
            border-color: #cbd5e1 !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.03);
        }

        .activity-ui input::placeholder,
        .activity-ui textarea::placeholder {
            color: #94a3b8 !important;
        }

        .activity-ui select option {
            background-color: #ffffff;
            color: #0f172a;
        }

        .dark .activity-ui input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        .dark .activity-ui select,
        .dark .activity-ui textarea {
            border-color: rgba(255, 255, 255, 0.10) !important;
            background-color: rgba(0, 0, 0, 0.20) !important;
            color: #f3f4f6 !important;
            box-shadow: none;
        }

        .dark .activity-ui select {
            background-color: #111827 !important;
        }

        .dark .activity-ui select option {
            background-color: #111827;
            color: #f9fafb;
        }

        .dark .activity-ui input::placeholder,
        .dark .activity-ui textarea::placeholder {
            color: #4b5563 !important;
        }

        .activity-ui input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):focus,
        .activity-ui select:focus,
        .activity-ui textarea:focus {
            outline: none;
            border-color: var(--activity-accent) !important;
            box-shadow: 0 0 0 3px rgb(16 185 129 / 0.12) !important;
        }

        .activity-ui input[type="datetime-local"] {
            min-height: 3.35rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
            color-scheme: light;
        }

        .activity-ui input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: .9;
            padding: .42rem;
            border-radius: .55rem;
            background-color: #f1f5f9;
            transition: background-color .2s ease, opacity .2s ease, outline-color .2s ease;
        }

        .activity-ui input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            background-color: #e2e8f0;
        }

        .dark .activity-ui input[type="datetime-local"] {
            color-scheme: dark;
        }

        .dark .activity-ui input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            background-color: rgba(255, 255, 255, 0.08);
        }

        .dark .activity-ui input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover {
            background-color: rgba(255, 255, 255, 0.13);
        }

        .activity-ui input[type="checkbox"],
        .activity-ui input[type="radio"] {
            border-color: #94a3b8;
            background-color: #ffffff;
            accent-color: var(--activity-accent);
        }

        .dark .activity-ui input[type="checkbox"],
        .dark .activity-ui input[type="radio"] {
            border-color: #4b5563;
            background-color: #1f2937;
        }

        .activity-ui [class~="text-emerald-300"] {
            color: #047857 !important;
        }

        .activity-ui [class~="text-emerald-400"] {
            color: #059669 !important;
        }

        .activity-ui [class~="text-blue-300"] {
            color: #1d4ed8 !important;
        }

        .activity-ui [class~="text-blue-400"] {
            color: #2563eb !important;
        }

        .activity-ui [class~="text-sky-300"] {
            color: #0369a1 !important;
        }

        .activity-ui [class~="text-sky-400"] {
            color: #0284c7 !important;
        }

        .activity-ui [class~="text-cyan-300"] {
            color: #0e7490 !important;
        }

        .activity-ui [class~="text-cyan-400"] {
            color: #0891b2 !important;
        }

        .activity-ui [class~="text-cyan-200"] {
            color: #0f766e !important;
        }

        .activity-ui [class~="text-amber-300"] {
            color: #b45309 !important;
        }

        .activity-ui [class~="text-amber-400"] {
            color: #d97706 !important;
        }

        .activity-ui [class~="text-orange-300"] {
            color: #c2410c !important;
        }

        .activity-ui [class~="text-orange-400"] {
            color: #ea580c !important;
        }

        .activity-ui [class~="text-red-300"] {
            color: #b91c1c !important;
        }

        .activity-ui [class~="text-red-400"] {
            color: #dc2626 !important;
        }

        .activity-ui [class~="text-violet-300"] {
            color: #6d28d9 !important;
        }

        .activity-ui [class~="text-violet-400"] {
            color: #7c3aed !important;
        }

        .dark .activity-ui [class~="text-emerald-300"] {
            color: #6ee7b7 !important;
        }

        .dark .activity-ui [class~="text-emerald-400"] {
            color: #34d399 !important;
        }

        .dark .activity-ui [class~="text-blue-300"] {
            color: #93c5fd !important;
        }

        .dark .activity-ui [class~="text-blue-400"] {
            color: #60a5fa !important;
        }

        .dark .activity-ui [class~="text-sky-300"] {
            color: #7dd3fc !important;
        }

        .dark .activity-ui [class~="text-sky-400"] {
            color: #38bdf8 !important;
        }

        .dark .activity-ui [class~="text-cyan-200"] {
            color: #a5f3fc !important;
        }

        .dark .activity-ui [class~="text-cyan-300"] {
            color: #67e8f9 !important;
        }

        .dark .activity-ui [class~="text-cyan-400"] {
            color: #22d3ee !important;
        }

        .dark .activity-ui [class~="text-amber-300"] {
            color: #fcd34d !important;
        }

        .dark .activity-ui [class~="text-amber-400"] {
            color: #fbbf24 !important;
        }

        .dark .activity-ui [class~="text-orange-300"] {
            color: #fdba74 !important;
        }

        .dark .activity-ui [class~="text-orange-400"] {
            color: #fb923c !important;
        }

        .dark .activity-ui [class~="text-red-300"] {
            color: #fca5a5 !important;
        }

        .dark .activity-ui [class~="text-red-400"] {
            color: #f87171 !important;
        }

        .dark .activity-ui [class~="text-violet-300"] {
            color: #c4b5fd !important;
        }

        .dark .activity-ui [class~="text-violet-400"] {
            color: #a78bfa !important;
        }

        .activity-ui [class*="bg-emerald-"][class~="text-white"],
        .activity-ui [class*="bg-blue-"][class~="text-white"],
        .activity-ui [class*="bg-sky-"][class~="text-white"],
        .activity-ui [class*="bg-cyan-"][class~="text-white"],
        .activity-ui [class*="bg-red-"][class~="text-white"],
        .activity-ui [class*="bg-violet-"][class~="text-white"] {
            color: #ffffff !important;
        }

        .activity-ui [class~="rounded-2xl"][class~="border"] {
            box-shadow: var(--activity-shadow);
        }

        .dark .activity-ui [class~="rounded-2xl"][class~="border"] {
            box-shadow: none;
        }

        .activity-ui .activity-force-dark [class~="text-white"],
        .activity-ui .activity-force-dark[class~="text-white"] {
            color: #ffffff !important;
        }

        .activity-ui .activity-force-dark [class~="text-gray-300"] {
            color: #d1d5db !important;
        }

        .activity-ui .activity-force-dark [class~="text-gray-400"] {
            color: #9ca3af !important;
        }

        .activity-ui .activity-force-dark [class~="border-white/10"] {
            border-color: rgba(255, 255, 255, .10) !important;
        }

        .activity-ui .activity-force-dark [class~="border-white/15"] {
            border-color: rgba(255, 255, 255, .15) !important;
        }

        .activity-ui .activity-force-dark [class~="bg-white/10"] {
            background-color: rgba(255, 255, 255, .10) !important;
        }

        .activity-ui .activity-force-dark [class~="hover:bg-white/20"]:hover {
            background-color: rgba(255, 255, 255, .20) !important;
        }

        .activity-ui .activity-stepbar {
            border-top: 0 !important;
            border-bottom: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            backdrop-filter: none !important;
        }

        .activity-ui .activity-step-caption {
            color: #0f766e !important;
        }

        .dark .activity-ui .activity-step-caption {
            color: #34d399 !important;
        }

        .activity-ui .activity-step-title,
        .activity-ui .activity-step-mobile-title {
            color: var(--activity-text) !important;
        }

        .activity-ui .activity-step-percent,
        .activity-ui .activity-step-mobile-count {
            color: var(--activity-text-muted) !important;
        }

        .activity-ui .activity-step-track {
            background: #dbe4ee !important;
        }

        .dark .activity-ui .activity-step-track {
            background: rgba(255, 255, 255, .10) !important;
        }

        .activity-ui .activity-step-meta span:last-child {
            color: var(--activity-text-muted) !important;
        }

        .activity-ui .activity-step-link:hover .activity-step-name {
            color: var(--activity-text) !important;
        }

        .activity-ui .activity-step-link .activity-step-name {
            color: var(--activity-text-muted) !important;
        }

        .dark .activity-ui .activity-step-link .activity-step-name {
            color: #9ca3af !important;
        }

        .activity-ui .activity-modal-panel {
            background: var(--activity-surface) !important;
            border-color: var(--activity-border) !important;
            box-shadow: var(--activity-shadow);
        }

        .activity-ui .activity-choice-card {
            position: relative;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .activity-ui .activity-choice-card:hover {
            border-color: #94a3b8 !important;
        }

        .dark .activity-ui .activity-choice-card:hover {
            border-color: rgba(255, 255, 255, .20) !important;
        }

        .activity-ui .activity-choice-card:has(input[type="radio"]:checked),
        .activity-ui .activity-choice-card:has(input[type="checkbox"]:checked),
        .activity-ui label.cursor-pointer:has(.peer:checked)>span,
        .activity-ui label.cursor-pointer:has(input[type="radio"]:checked)>span,
        .activity-ui label.cursor-pointer:has(input[type="checkbox"]:checked)>span {
            border-color: rgb(16 185 129 / 0.55) !important;
            background-color: var(--activity-accent-soft) !important;
            box-shadow: 0 0 0 1px var(--activity-accent-ring) inset;
        }

        .activity-ui .activity-choice-card:has(input[type="radio"]:checked) [class~="text-white"],
        .activity-ui .activity-choice-card:has(input[type="checkbox"]:checked) [class~="text-white"],
        .activity-ui label.cursor-pointer:has(.peer:checked)>span [class~="text-white"],
        .activity-ui label.cursor-pointer:has(input[type="radio"]:checked)>span [class~="text-white"],
        .activity-ui label.cursor-pointer:has(input[type="checkbox"]:checked)>span [class~="text-white"] {
            color: #065f46 !important;
        }

        .activity-ui .activity-choice-card:has(input[type="radio"]:checked) [class~="text-gray-500"],
        .activity-ui .activity-choice-card:has(input[type="checkbox"]:checked) [class~="text-gray-500"],
        .activity-ui .activity-choice-card:has(input[type="radio"]:checked) [class~="text-gray-600"],
        .activity-ui .activity-choice-card:has(input[type="checkbox"]:checked) [class~="text-gray-600"],
        .activity-ui label.cursor-pointer:has(.peer:checked)>span [class~="text-gray-500"],
        .activity-ui label.cursor-pointer:has(.peer:checked)>span [class~="text-gray-600"],
        .activity-ui label.cursor-pointer:has(input[type="radio"]:checked)>span [class~="text-gray-500"],
        .activity-ui label.cursor-pointer:has(input[type="checkbox"]:checked)>span [class~="text-gray-500"] {
            color: #047857 !important;
        }

        .dark .activity-ui .activity-choice-card:has(input[type="radio"]:checked) [class~="text-white"],
        .dark .activity-ui .activity-choice-card:has(input[type="checkbox"]:checked) [class~="text-white"],
        .dark .activity-ui label.cursor-pointer:has(.peer:checked)>span [class~="text-white"],
        .dark .activity-ui label.cursor-pointer:has(input[type="radio"]:checked)>span [class~="text-white"],
        .dark .activity-ui label.cursor-pointer:has(input[type="checkbox"]:checked)>span [class~="text-white"] {
            color: #ecfdf5 !important;
        }

        .dark .activity-ui .activity-choice-card:has(input[type="radio"]:checked) [class~="text-gray-500"],
        .dark .activity-ui .activity-choice-card:has(input[type="checkbox"]:checked) [class~="text-gray-500"],
        .dark .activity-ui .activity-choice-card:has(input[type="radio"]:checked) [class~="text-gray-600"],
        .dark .activity-ui .activity-choice-card:has(input[type="checkbox"]:checked) [class~="text-gray-600"],
        .dark .activity-ui label.cursor-pointer:has(.peer:checked)>span [class~="text-gray-500"],
        .dark .activity-ui label.cursor-pointer:has(.peer:checked)>span [class~="text-gray-600"],
        .dark .activity-ui label.cursor-pointer:has(input[type="radio"]:checked)>span [class~="text-gray-500"],
        .dark .activity-ui label.cursor-pointer:has(input[type="checkbox"]:checked)>span [class~="text-gray-500"] {
            color: #a7f3d0 !important;
        }

        .activity-ui .activity-choice-card:has(input:disabled) {
            opacity: .75;
        }

        .activity-ui .activity-date-input,
        .activity-ui input.future-date,
        .activity-ui input[type="datetime-local"] {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .dark .activity-ui .activity-date-input,
        .dark .activity-ui input.future-date,
        .dark .activity-ui input[type="datetime-local"] {
            background-color: rgba(0, 0, 0, .20) !important;
            color: #f8fafc !important;
        }

        .activity-ui .activity-soft-info {
            border-color: rgb(14 165 233 / .22) !important;
            background-color: rgb(14 165 233 / .08) !important;
        }

        .activity-ui .activity-soft-success {
            border-color: rgb(16 185 129 / .22) !important;
            background-color: rgb(16 185 129 / .08) !important;
        }


        /* Paneles informativos suaves: más contraste en modo claro sin saturar el diseño. */
        .activity-ui [class~="bg-cyan-500/[0.03]"],
        .activity-ui [class~="bg-cyan-500/[0.04]"],
        .activity-ui [class~="bg-cyan-500/[0.05]"],
        .activity-ui [class~="bg-cyan-500/[0.06]"] {
            background-color: #ecfeff !important;
        }

        .activity-ui [class~="border-cyan-500/10"],
        .activity-ui [class~="border-cyan-500/15"],
        .activity-ui [class~="border-cyan-500/20"] {
            border-color: #a5f3fc !important;
        }

        .activity-ui [class~="bg-blue-500/[0.025]"],
        .activity-ui [class~="bg-blue-500/[0.03]"],
        .activity-ui [class~="bg-blue-500/[0.04]"],
        .activity-ui [class~="bg-blue-500/[0.05]"] {
            background-color: #eff6ff !important;
        }

        .activity-ui [class~="border-blue-500/10"],
        .activity-ui [class~="border-blue-500/15"],
        .activity-ui [class~="border-blue-500/20"] {
            border-color: #bfdbfe !important;
        }

        .activity-ui [class~="text-cyan-200/80"] {
            color: #0e7490 !important;
        }

        .activity-ui [class~="text-blue-200/80"] {
            color: #1d4ed8 !important;
        }

        .dark .activity-ui [class~="bg-cyan-500/[0.03]"] {
            background-color: rgb(6 182 212 / .03) !important;
        }

        .dark .activity-ui [class~="bg-cyan-500/[0.04]"] {
            background-color: rgb(6 182 212 / .04) !important;
        }

        .dark .activity-ui [class~="bg-cyan-500/[0.05]"] {
            background-color: rgb(6 182 212 / .05) !important;
        }

        .dark .activity-ui [class~="bg-cyan-500/[0.06]"] {
            background-color: rgb(6 182 212 / .06) !important;
        }

        .dark .activity-ui [class~="border-cyan-500/10"] {
            border-color: rgb(6 182 212 / .10) !important;
        }

        .dark .activity-ui [class~="border-cyan-500/15"] {
            border-color: rgb(6 182 212 / .15) !important;
        }

        .dark .activity-ui [class~="border-cyan-500/20"] {
            border-color: rgb(6 182 212 / .20) !important;
        }

        .dark .activity-ui [class~="text-cyan-200/80"] {
            color: rgb(165 243 252 / .88) !important;
        }

        .dark .activity-ui [class~="bg-blue-500/[0.025]"] {
            background-color: rgb(59 130 246 / .025) !important;
        }

        .dark .activity-ui [class~="bg-blue-500/[0.03]"] {
            background-color: rgb(59 130 246 / .03) !important;
        }

        .dark .activity-ui [class~="bg-blue-500/[0.04]"] {
            background-color: rgb(59 130 246 / .04) !important;
        }

        .dark .activity-ui [class~="bg-blue-500/[0.05]"] {
            background-color: rgb(59 130 246 / .05) !important;
        }

        .dark .activity-ui [class~="border-blue-500/10"] {
            border-color: rgb(59 130 246 / .10) !important;
        }

        .dark .activity-ui [class~="border-blue-500/15"] {
            border-color: rgb(59 130 246 / .15) !important;
        }

        .dark .activity-ui [class~="border-blue-500/20"] {
            border-color: rgb(59 130 246 / .20) !important;
        }

        .dark .activity-ui [class~="text-blue-200/80"] {
            color: rgb(191 219 254 / .88) !important;
        }


        /* Radios personalizados: el estado activo se marca con un punto interior, no con un halo exterior. */
        .activity-ui input[type="radio"] {
            -webkit-appearance: none !important;
            appearance: none !important;
            width: 1rem;
            height: 1rem;
            min-width: 1rem;
            min-height: 1rem;
            margin: 0.15rem 0 0;
            border: 1.5px solid #94a3b8 !important;
            border-radius: 9999px;
            background: #ffffff !important;
            box-shadow: none !important;
            cursor: pointer;
            vertical-align: middle;
            transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
        }

        .activity-ui input[type="radio"]:hover {
            border-color: #10b981 !important;
        }

        .activity-ui input[type="radio"]:checked {
            border-color: #10b981 !important;
            background:
                radial-gradient(circle at center, #10b981 0 34%, transparent 36% 100%),
                #ffffff !important;
            box-shadow: none !important;
        }

        .activity-ui input[type="radio"]:focus,
        .activity-ui input[type="radio"]:focus-visible {
            outline: none !important;
            box-shadow: 0 0 0 3px rgb(16 185 129 / .14) !important;
        }

        .activity-ui input[type="radio"]:disabled {
            cursor: not-allowed;
            opacity: .5;
        }

        .dark .activity-ui input[type="radio"] {
            border-color: #64748b !important;
            background: #0f172a !important;
        }

        .dark .activity-ui input[type="radio"]:checked {
            border-color: #34d399 !important;
            background:
                radial-gradient(circle at center, #34d399 0 34%, transparent 36% 100%),
                #0f172a !important;
        }

        .dark .activity-ui input[type="radio"]:focus,
        .dark .activity-ui input[type="radio"]:focus-visible {
            box-shadow: 0 0 0 3px rgb(52 211 153 / .16) !important;
        }

        @media (prefers-reduced-motion: reduce) {

            .activity-ui *,
            .activity-ui *::before,
            .activity-ui *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
@endonce
