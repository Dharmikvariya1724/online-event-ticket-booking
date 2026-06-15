@extends('layouts.organiser')
@section('title', 'Create Event')
@section('page-title', 'Create New Event')
@section('page-subtitle', 'Fill in the details for your new event')
@section('body-class', 'event-create-page')

@section('head')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
  .event-create-page {
    background-image: none !important;
  }

  .event-create-shell {
    max-width: 64rem;
    margin: 0;
    width: 100%;
  }

  .event-create-form {
    display: grid;
    gap: 1rem;
  }

  .event-card {
    background: var(--event-card-bg);
    border: 1px solid var(--event-card-border);
    border-radius: 1rem;
    box-shadow: var(--event-card-shadow);
    padding: 1rem;
  }

  .event-card__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .event-card__title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
  }

  .event-card__icon {
    width: 1.15rem;
    height: 1.15rem;
    color: #7c3aed;
    flex-shrink: 0;
    margin-top: 0.12rem;
  }

  .event-card__icon svg {
    width: 100%;
    height: 100%;
  }

  .event-card__title {
    font-size: 1.02rem;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.02em;
    color: var(--event-heading);
  }

  .event-card__subtitle {
    margin-top: 0.18rem;
    font-size: 0.74rem;
    color: var(--event-muted);
  }

  .event-grid {
    display: grid;
    gap: 0.85rem;
  }

  .event-grid--2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .event-grid--3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .event-field {
    display: grid;
    gap: 0.4rem;
    min-width: 0;
  }

  .event-field--span-2 {
    grid-column: span 2;
  }

  .event-label {
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1.2;
    color: var(--event-label);
  }

  .event-input,
  .event-select,
  .event-textarea {
    width: 100%;
    border: 1px solid var(--event-input-border);
    border-radius: 0.45rem;
    background: var(--event-input-bg);
    color: var(--event-heading);
    font-size: 0.8rem;
    line-height: 1.5;
    padding: 0.62rem 0.75rem;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .event-input::placeholder,
  .event-textarea::placeholder {
    color: var(--event-placeholder);
  }

  .event-datetime-wrap {
    position: relative;
  }

  .event-input--datetime {
    padding-right: 2.65rem;
  }

  .event-input--datetime::-webkit-calendar-picker-indicator {
    opacity: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: 2.75rem;
    height: 100%;
    margin: 0;
    cursor: pointer;
  }

  .event-input--native-datetime {
    position: absolute;
    inset: 0;
    opacity: 0;
    pointer-events: none;
    padding-right: 0.85rem;
  }

  .event-input--native-datetime::-webkit-calendar-picker-indicator {
    opacity: 0;
  }

  .event-input--datetime-display {
    padding-right: 2.65rem;
    cursor: pointer;
  }

  .event-datetime-wrap--native .event-datetime-trigger {
    display: inline-flex;
  }

  .event-datetime-trigger {
    position: absolute;
    top: 50%;
    right: 0.8rem;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.1rem;
    height: 1.1rem;
    padding: 0;
    border: 0;
    background: transparent;
    color: var(--event-datetime-trigger);
    cursor: pointer;
  }

  .event-datetime-trigger svg {
    width: 100%;
    height: 100%;
  }

  .flatpickr-calendar {
    border-radius: 0.9rem;
    border: 1px solid var(--event-calendar-border);
    box-shadow: 0 22px 55px rgba(15, 23, 42, 0.18);
    overflow: hidden;
    background: var(--event-calendar-bg);
    color: var(--event-calendar-text);
    z-index: 120 !important;
  }

  .flatpickr-months,
  .flatpickr-weekdays {
    background: var(--event-calendar-header);
    color: var(--event-calendar-header-text);
  }

  .flatpickr-weekday,
  .flatpickr-current-month,
  .flatpickr-current-month .flatpickr-monthDropdown-months,
  .flatpickr-current-month input.cur-year,
  .flatpickr-months .flatpickr-prev-month,
  .flatpickr-months .flatpickr-next-month {
    color: var(--event-calendar-header-text);
    fill: var(--event-calendar-header-text);
  }

  .flatpickr-current-month {
    padding-top: 0.2rem;
  }

  .flatpickr-current-month .flatpickr-monthDropdown-months,
  .flatpickr-current-month input.cur-year {
    font-weight: 700;
  }

  .flatpickr-day.selected,
  .flatpickr-day.startRange,
  .flatpickr-day.endRange,
  .flatpickr-day.selected:hover,
  .flatpickr-day.startRange:hover,
  .flatpickr-day.endRange:hover {
    background: var(--event-calendar-accent);
    border-color: var(--event-calendar-accent);
  }

  .flatpickr-day:hover,
  .flatpickr-day:focus {
    background: var(--event-calendar-hover);
    border-color: var(--event-calendar-hover);
  }

  .flatpickr-day.today {
    border-color: var(--event-calendar-accent);
  }

  .flatpickr-time {
    border-top: 1px solid var(--event-calendar-border);
    background: var(--event-calendar-bg);
  }

  .flatpickr-time input:hover,
  .flatpickr-time .flatpickr-am-pm:hover,
  .flatpickr-time input:focus,
  .flatpickr-time .flatpickr-am-pm:focus,
  .flatpickr-current-month .flatpickr-monthDropdown-months:hover,
  .flatpickr-current-month input.cur-year:hover {
    background: var(--event-calendar-hover);
  }

  .event-input:focus,
  .event-select:focus,
  .event-textarea:focus {
    border-color: rgba(124, 58, 237, 0.45);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
  }

  .event-textarea {
    min-height: 4.75rem;
    resize: vertical;
  }

  .event-upload {
    border: 1px solid var(--event-card-border);
    border-radius: 0.85rem;
    overflow: hidden;
    background: var(--event-upload-bg);
    margin-bottom: 1rem;
  }

  .event-upload__band {
    height: 1.9rem;
    background: linear-gradient(90deg, #6d28d9 0%, #c026d3 55%, #ec4899 100%);
  }

  .event-upload__body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem;
  }

  .event-upload__title {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--event-heading);
    margin-bottom: 0.3rem;
  }

  .event-upload__copy {
    font-size: 0.73rem;
    color: var(--event-muted);
    max-width: 23rem;
    line-height: 1.6;
  }

  .event-upload__action {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.62rem 0.95rem;
    border: 1px solid rgba(124, 58, 237, 0.35);
    border-radius: 0.55rem;
    background: #ffffff;
    color: #7c3aed;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
  }

  .event-upload__action input {
    display: none;
  }

  .event-upload__meta {
    margin-top: 0.2rem;
    font-size: 0.65rem;
    color: var(--event-placeholder);
  }

  .event-hint {
    font-size: 0.7rem;
    color: var(--event-muted);
    text-align: right;
    margin: -0.35rem 0 0.85rem;
  }

  .event-inline-note {
    font-size: 0.68rem;
    color: var(--event-muted);
  }

  .event-validation-notes {
    display: grid;
    justify-items: end;
    gap: 0.25rem;
    grid-column: 1 / -1;
    text-align: right;
  }

  .event-field-error {
    display: none;
    font-size: 0.7rem;
    color: #dc2626;
  }

  .event-field-error.is-visible {
    display: block;
  }

  .event-section-divider {
    height: 1px;
    background: var(--event-divider);
    margin: 0.2rem 0 0.55rem;
  }

  .event-lineup-list {
    display: grid;
    gap: 0.7rem;
  }

  .event-lineup-row {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1.5fr) minmax(0, 1fr) 2.6rem;
    gap: 0.65rem;
    align-items: end;
  }

  .event-icon-btn,
  .event-add-btn,
  .event-secondary-btn,
  .event-primary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    border-radius: 0.55rem;
    font-size: 0.75rem;
    font-weight: 700;
    transition: all 0.2s ease;
  }

  .event-icon-btn {
    width: 2.6rem;
    height: 2.55rem;
    border: 1px solid var(--event-input-border);
    background: var(--event-input-bg);
    color: var(--event-muted);
  }

  .event-add-btn,
  .event-secondary-btn {
    border: 1px solid var(--event-input-border);
    background: var(--event-input-bg);
    color: var(--event-heading);
    padding: 0.62rem 0.95rem;
  }

  .event-primary-btn {
    border: 0;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: #ffffff;
    padding: 0.7rem 1rem;
    box-shadow: 0 12px 24px rgba(124, 58, 237, 0.22);
  }

  .event-actions {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    justify-content: flex-end;
    padding-top: 0.2rem;
  }

  .event-error-card {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.18);
    border-radius: 1rem;
    padding: 0.9rem 1rem;
  }

  .event-error-card ul {
    margin: 0;
    padding-left: 1.15rem;
    display: grid;
    gap: 0.35rem;
  }

  .event-error-card li {
    color: #dc2626;
    font-size: 0.8rem;
  }

  .event-status-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
    margin-bottom: 0.85rem;
  }

  .event-status-wrap .event-field {
    width: 100%;
  }

  .event-status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.7rem;
    border-radius: 999px;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 0.68rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .event-create-page .ck.ck-editor {
    width: 100% !important;
    min-width: 100%;
    max-width: 100%;
    overflow: hidden;
    box-sizing: border-box;
  }

  .event-create-page .ck.ck-editor__main {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
  }

  .event-create-page .ck.ck-toolbar {
    border: 1px solid var(--event-input-border);
    border-bottom: 0;
    border-radius: 0.75rem 0.75rem 0 0;
    background: var(--event-input-bg);
  }

  .event-create-page .ck.ck-toolbar .ck-toolbar__separator {
    background: var(--event-input-border);
  }

  .event-create-page .ck.ck-editor__main > .ck-editor__editable {
    min-height: 11rem !important;
    height: 11rem !important;
    max-height: 11rem !important;
    width: 100% !important;
    min-width: 0;
    max-width: 100%;
    overflow-x: hidden;
    overflow-y: auto;
    word-break: break-word;
    overflow-wrap: anywhere;
    resize: none;
    box-sizing: border-box;
    border: 1px solid var(--event-input-border);
    border-radius: 0 0 0.75rem 0.75rem;
    background: var(--event-input-bg);
    color: var(--event-heading);
    box-shadow: none;
  }

  .event-create-page .ck.ck-editor__main > .ck-editor__editable:focus {
    border-color: rgba(124, 58, 237, 0.45);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
  }

  .event-create-page .ck.ck-editor__main > .ck-editor__editable p,
  .event-create-page .ck.ck-editor__main > .ck-editor__editable li,
  .event-create-page .ck.ck-editor__main > .ck-editor__editable blockquote {
    color: inherit;
  }

  .event-create-page .ck.ck-editor__main > .ck-editor__editable h1,
  .event-create-page .ck.ck-editor__main > .ck-editor__editable h2,
  .event-create-page .ck.ck-editor__main > .ck-editor__editable h3 {
    color: var(--event-heading);
    margin: 0.8rem 0 0.45rem;
  }

  .event-create-page .ck.ck-editor__main > .ck-editor__editable h1 {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.25;
  }

  .event-create-page .ck.ck-editor__main > .ck-editor__editable h2 {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.3;
  }

  .event-create-page .ck.ck-editor__main > .ck-editor__editable h3 {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.35;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button .ck-button__label,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__button,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__panel {
    color: #e2e8f0 !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button .ck-icon,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button .ck-icon *,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__arrow {
    color: #e2e8f0 !important;
    fill: currentColor !important;
    stroke: currentColor !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button:hover:not(.ck-disabled),
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button:focus:not(.ck-disabled),
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button.ck-on,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__button:hover:not(.ck-disabled),
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__button:focus:not(.ck-disabled) {
    background: rgba(139, 92, 246, 0.18) !important;
    color: #f8fafc !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button:hover:not(.ck-disabled) .ck-icon,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button:hover:not(.ck-disabled) .ck-icon *,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button:focus:not(.ck-disabled) .ck-icon,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button:focus:not(.ck-disabled) .ck-icon *,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button.ck-on .ck-icon,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-button.ck-on .ck-icon *,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__button:hover:not(.ck-disabled) .ck-icon,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__button:hover:not(.ck-disabled) .ck-icon *,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__button:focus:not(.ck-disabled) .ck-icon,
  :root[data-theme='dark'] .event-create-page .ck.ck-toolbar .ck-dropdown__button:focus:not(.ck-disabled) .ck-icon * {
    color: #f8fafc !important;
    fill: currentColor !important;
    stroke: currentColor !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-editor__main > .ck-editor__editable,
  :root[data-theme='dark'] .event-create-page .ck.ck-editor__main > .ck-editor__editable p,
  :root[data-theme='dark'] .event-create-page .ck.ck-editor__main > .ck-editor__editable li,
  :root[data-theme='dark'] .event-create-page .ck.ck-editor__main > .ck-editor__editable blockquote {
    color: #e2e8f0 !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-editor__main > .ck-editor__editable h1,
  :root[data-theme='dark'] .event-create-page .ck.ck-editor__main > .ck-editor__editable h2,
  :root[data-theme='dark'] .event-create-page .ck.ck-editor__main > .ck-editor__editable h3 {
    color: #f8fafc !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel {
    background: #0f172a !important;
    border: 1px solid #334155 !important;
    box-shadow: 0 18px 45px rgba(2, 6, 23, 0.38) !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-list,
  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-list__item,
  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-list__item .ck-button,
  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-list__item .ck-button .ck-button__label {
    background: transparent !important;
    color: #e2e8f0 !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-icon,
  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-icon * {
    color: #e2e8f0 !important;
    fill: currentColor !important;
    stroke: currentColor !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-list__item .ck-button:hover:not(.ck-disabled),
  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-list__item .ck-button:focus:not(.ck-disabled),
  :root[data-theme='dark'] .event-create-page .ck.ck-dropdown__panel .ck-list__item .ck-button.ck-on {
    background: rgba(139, 92, 246, 0.18) !important;
    color: #f8fafc !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-heading-dropdown .ck-list__item .ck-button_heading1 .ck-button__label {
    color: #f8fafc !important;
    font-size: 1.05rem !important;
    font-weight: 800 !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-heading-dropdown .ck-list__item .ck-button_heading2 .ck-button__label {
    color: #f8fafc !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
  }

  :root[data-theme='dark'] .event-create-page .ck.ck-heading-dropdown .ck-list__item .ck-button_heading3 .ck-button__label {
    color: #f8fafc !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
  }

  @media (max-width: 767px) {
    .event-create-shell {
      max-width: 100%;
    }

    .event-grid--2,
    .event-grid--3,
    .event-lineup-row {
      grid-template-columns: 1fr;
    }

    .event-field--span-2 {
      grid-column: auto;
    }

    .event-upload__body,
    .event-status-wrap,
    .event-actions,
    .event-card__head {
      flex-direction: column;
      align-items: stretch;
    }

    .event-hint {
      text-align: left;
      margin-top: 0;
    }

    .event-validation-notes {
      justify-items: start;
      text-align: left;
    }

    .event-actions > * {
      width: 100%;
    }

    .event-icon-btn {
      width: 100%;
    }
  }

  @media (max-width: 479px) {
    .event-card {
      padding: 0.85rem;
    }

    .event-card__title {
      font-size: 0.94rem;
    }

    .event-upload__body {
      padding: 0.85rem;
    }

    .event-upload__action,
    .event-add-btn,
    .event-secondary-btn,
    .event-primary-btn {
      width: 100%;
    }
  }

  :root[data-theme='light'] .event-create-page {
    background: #ffffff !important;
    --event-card-bg: #ffffff;
    --event-card-border: #d8dde7;
    --event-card-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    --event-heading: #111827;
    --event-muted: #7b8192;
    --event-label: #111827;
    --event-divider: #e5e7eb;
    --event-input-bg: #ffffff;
    --event-input-border: #d7dce5;
    --event-placeholder: #9ca3af;
    --event-datetime-trigger: #111827;
    --event-upload-bg: #ffffff;
    --event-calendar-bg: #ffffff;
    --event-calendar-border: #d8dde7;
    --event-calendar-header: linear-gradient(135deg, #312e81, #6d28d9);
    --event-calendar-header-text: #ffffff;
    --event-calendar-text: #111827;
    --event-calendar-accent: #6d28d9;
    --event-calendar-hover: rgba(109, 40, 217, 0.08);
  }

  :root[data-theme='dark'] .event-create-page {
    background: #060b14 !important;
    --event-card-bg: #101827;
    --event-card-border: #243043;
    --event-card-shadow: none;
    --event-heading: #f8fafc;
    --event-muted: #94a3b8;
    --event-label: #e2e8f0;
    --event-divider: #243043;
    --event-input-bg: #0f172a;
    --event-input-border: #334155;
    --event-placeholder: #64748b;
    --event-datetime-trigger: #f8fafc;
    --event-upload-bg: #0f172a;
    --event-calendar-bg: #101827;
    --event-calendar-border: #334155;
    --event-calendar-header: linear-gradient(135deg, #111827, #312e81);
    --event-calendar-header-text: #f8fafc;
    --event-calendar-text: #e2e8f0;
    --event-calendar-accent: #8b5cf6;
    --event-calendar-hover: rgba(139, 92, 246, 0.14);
  }

  :root[data-theme='dark'] .event-upload__action,
  :root[data-theme='dark'] .event-status-chip {
    background: #0f172a;
  }


</style>
@endsection

@section('content')
@php
    $lineupNames = old('lineup_names', ['']);
    $lineupRoles = old('lineup_roles', ['']);
    $lineupTimes = old('lineup_times', ['']);
    $lineupCount = max(count($lineupNames), count($lineupRoles), count($lineupTimes), 1);
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <a href="{{ route('organiser.events.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-gray-400 hover:text-white transition-colors">
      <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-700 bg-transparent text-gray-400 transition-colors group-hover:border-gray-500">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6 9 12l6 6"></path>
        </svg>
      </span>
      <span>Back to events</span>
    </a>
  
</div>

<div class="event-create-shell">
<form id="event-form" action="{{ route('organiser.events.store') }}" method="POST" enctype="multipart/form-data" class="event-create-form" novalidate>
@csrf

@if($errors->any())
<div class="event-error-card">
  <ul>
    @foreach($errors->all() as $e)
    <li>{{ $e }}</li>
    @endforeach
  </ul>
</div>
@endif

<section class="event-card">
  <div class="event-card__head">
    <div class="event-card__title-wrap">
      <span class="event-card__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 8.25h.01M12 12h.01M12 15.75h.01M10 4.75h4a5.25 5.25 0 0 1 5.25 5.25v4A5.25 5.25 0 0 1 14 19.25h-4A5.25 5.25 0 0 1 4.75 14v-4A5.25 5.25 0 0 1 10 4.75Z"/></svg>
      </span>
      <div>
        <h2 class="event-card__title">Basic Information</h2>
        <p class="event-card__subtitle">Enter the main details about your event</p>
      </div>
    </div>
  </div>

  <div class="event-upload">
    <div class="event-upload__band"></div>
    <div class="event-upload__body">
      <div>
        <div class="event-upload__title">AI Image</div>
        <div class="event-upload__copy">Upload your poster and our AI will automatically extract details (title, date, venue, description) and prefill the form.</div>
      </div>
      <div class="js-file-upload-scope">
        <label class="event-upload__action">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V8m0 0-3 3m3-3 3 3M6.75 16.75v.5A1.75 1.75 0 0 0 8.5 19h7a1.75 1.75 0 0 0 1.75-1.75v-.5"/></svg>
          <span>Upload AI image</span>
          <input type="file" name="banner_image" id="banner_image" class="js-ai-image-input" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" data-poster-autofill-url="{{ route('organiser.events.poster.parse') }}" data-file-name-selector=".js-ai-image-file-name">
        </label>
        <div class="event-upload__meta"><span class="js-ai-image-file-name">No file selected</span><br>PNG, JPG, WebP · Max 4MB</div>
      </div>
    </div>
  </div>

  <div class="event-hint">AI image is only used to autofill the form. Event Poster below remains your original listing banner.</div>

  <div class="event-grid">
    <div class="event-field">
      <label class="event-label">Event Poster</label>
      <div class="js-file-upload-scope">
        <label class="event-upload__action">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V8m0 0-3 3m3-3 3 3M6.75 16.75v.5A1.75 1.75 0 0 0 8.5 19h7a1.75 1.75 0 0 0 1.75-1.75v-.5"/></svg>
          <span>Upload poster</span>
          <input type="file" name="banner" id="banner" class="js-banner-input" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" data-file-name-selector=".js-banner-file-name">
        </label>
        <div class="event-upload__meta"><span class="js-banner-file-name">No file selected</span><br>PNG, JPG, WebP · Max 4MB</div>
      </div>
      <div class="event-inline-note">This poster is saved as the original event banner and shown across listings.</div>
    </div>

    <div class="event-field">
      <label class="event-label">Event Title *</label>
      <input type="text" name="title" value="{{ old('title') }}" required maxlength="50" class="event-input" placeholder="e.g. Summer Music Festival 2025">
    </div>

    <div class="event-field">
      <label class="event-label">Short Description</label>
      <input type="text" name="short_description" value="{{ old('short_description') }}" maxlength="255" class="event-input" placeholder="One-line summary shown in listings">
    </div>

    <div class="event-field">
      <label class="event-label">Category *</label>
      <select name="category" class="event-select">
        @foreach(\App\Models\Event::CATEGORIES as $cat)
        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </select>
    </div>

  <div class="event-status-wrap">
    <input type="hidden" name="status" value="draft">

<div class="event-field">
  <label class="event-label">Status</label>
  <select class="event-select" disabled>
    <option value="draft" selected>Draft</option>
  </select>
</div>
    <div class="event-field">
      <label class="event-label">Feature on Homepage</label>
      <label class="inline-flex items-center gap-2 text-sm text-gray-400">
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-500 text-violet-600 focus:ring-violet-500">
        <span>Mark as featured event</span>
      </label>
    </div>
    <!-- <span class="event-status-chip">Current setup for new event creation</span> -->
  </div>
  
    <div class="event-field">
      <label class="event-label">Full Description</label>
      <textarea id="description" name="description" rows="6" maxlength="5000" class="event-textarea">{{ old('description') }}</textarea>
      <div class="event-inline-note">Maximum 5000 characters.</div>
    </div>
  </div>
</section>

<section class="event-card">
  <div class="event-card__head">
    <div class="event-card__title-wrap">
      <span class="event-card__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M7.75 4.75v3.5m8.5-3.5v3.5M5 8.25h14M6.5 19.25h11a1.75 1.75 0 0 0 1.75-1.75v-9a1.75 1.75 0 0 0-1.75-1.75h-11A1.75 1.75 0 0 0 4.75 8.5v9A1.75 1.75 0 0 0 6.5 19.25Z"/></svg>
      </span>
      <div>
        <h2 class="event-card__title">Date &amp; Time</h2>
        <p class="event-card__subtitle">Choose when your event starts and ends. Tickets can only be validated within this time window.</p>
      </div>
    </div>
  </div>

  <div class="event-grid event-grid--2">
    <div class="event-field">
      <label class="event-label">Start Date &amp; Time *</label>
      <div class="event-datetime-wrap">
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required class="event-input event-input--datetime js-datetime-input" autocomplete="off">
        <button type="button" class="event-datetime-trigger" onclick="openDateTimePicker(event, this)" aria-label="Open start date and time picker">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.75 4.75v3.5m8.5-3.5v3.5M5 8.25h14M6.5 19.25h11a1.75 1.75 0 0 0 1.75-1.75v-9A1.75 1.75 0 0 0 17.5 6.75h-11A1.75 1.75 0 0 0 4.75 8.5v9A1.75 1.75 0 0 0 6.5 19.25Z"/></svg>
        </button>
      </div>
    </div>
    <div class="event-field">
      <label class="event-label">End Date &amp; Time *</label>
      <div class="event-datetime-wrap">
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required class="event-input event-input--datetime js-datetime-input" autocomplete="off">
        <button type="button" class="event-datetime-trigger" onclick="openDateTimePicker(event, this)" aria-label="Open end date and time picker">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.75 4.75v3.5m8.5-3.5v3.5M5 8.25h14M6.5 19.25h11a1.75 1.75 0 0 0 1.75-1.75v-9A1.75 1.75 0 0 0 17.5 6.75h-11A1.75 1.75 0 0 0 4.75 8.5v9A1.75 1.75 0 0 0 6.5 19.25Z"/></svg>
        </button>
      </div>
    </div>
    <div class="event-field">
      <label class="event-label">Start Validate Ticket *</label>
      <div class="event-datetime-wrap">
        <input type="datetime-local" id="ticket_validation_starts_at" name="ticket_validation_starts_at" value="{{ old('ticket_validation_starts_at') }}" required class="event-input event-input--datetime js-datetime-input" autocomplete="off">
        <button type="button" class="event-datetime-trigger" onclick="openDateTimePicker(event, this)" aria-label="Open validation start date and time picker">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.75 4.75v3.5m8.5-3.5v3.5M5 8.25h14M6.5 19.25h11a1.75 1.75 0 0 0 1.75-1.75v-9A1.75 1.75 0 0 0 17.5 6.75h-11A1.75 1.75 0 0 0 4.75 8.5v9A1.75 1.75 0 0 0 6.5 19.25Z"/></svg>
        </button>
      </div>
    </div>
    <div class="event-field">
      <label class="event-label">End Validate Ticket *</label>
      <div class="event-datetime-wrap">
        <input type="datetime-local" id="ticket_validation_ends_at" name="ticket_validation_ends_at" value="{{ old('ticket_validation_ends_at') }}" required class="event-input event-input--datetime js-datetime-input" autocomplete="off">
        <button type="button" class="event-datetime-trigger" onclick="openDateTimePicker(event, this)" aria-label="Open validation end date and time picker">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.75 4.75v3.5m8.5-3.5v3.5M5 8.25h14M6.5 19.25h11a1.75 1.75 0 0 0 1.75-1.75v-9A1.75 1.75 0 0 0 17.5 6.75h-11A1.75 1.75 0 0 0 4.75 8.5v9A1.75 1.75 0 0 0 6.5 19.25Z"/></svg>
        </button>
      </div>
    </div>
    <div class="event-validation-notes">
      <div class="event-inline-note">Default: event end date and time. You can change it manually.</div>
      <div class="event-inline-note">Default: 2 hours before event start date and time.</div>
    </div>
  </div>
  <div class="event-hint">Validation works between the selected validation start and end date-time.</div>
</section>

<section class="event-card">
  <div class="event-card__head">
    <div class="event-card__title-wrap">
      <span class="event-card__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 20s6.25-5.07 6.25-10A6.25 6.25 0 1 0 5.75 10c0 4.93 6.25 10 6.25 10Zm0-7.25A2.75 2.75 0 1 0 12 7.25a2.75 2.75 0 0 0 0 5.5Z"/></svg>
      </span>
      <div>
        <h2 class="event-card__title">Location</h2>
        <p class="event-card__subtitle">Where will your event take place?</p>
      </div>
    </div>
  </div>

  <div class="event-grid event-grid--2">
    <div class="event-field">
      <label class="event-label">Venue Name *</label>
      <input type="text" name="venue_name" value="{{ old('venue_name') }}" required maxlength="50" class="event-input" placeholder="O2 Arena">
    </div>
    <div class="event-field">
      <label class="event-label">City *</label>
      <input type="text" name="city" value="{{ old('city') }}" required maxlength="50" class="event-input" placeholder="London">
    </div>
    <div class="event-field event-field--span-2">
      <label class="event-label">Address *</label>
      <input type="text" name="venue_address" value="{{ old('venue_address') }}" required maxlength="300" class="event-input" placeholder="Peninsula Square">
    </div>
    <div class="event-field">
      <label class="event-label">Postcode</label>
      <input type="text" name="postcode" value="{{ old('postcode') }}" maxlength="10" class="event-input" placeholder="SE10 0DX">
    </div>
  </div>
</section>

<section class="event-card">
  <div class="event-card__head">
    <div class="event-card__title-wrap">
      <span class="event-card__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M5.75 8.5h12.5M5.75 12h12.5M5.75 15.5h7.5M6.5 19.25h11a1.75 1.75 0 0 0 1.75-1.75v-11A1.75 1.75 0 0 0 17.5 4.75h-11A1.75 1.75 0 0 0 4.75 6.5v11A1.75 1.75 0 0 0 6.5 19.25Z"/></svg>
      </span>
      <div>
        <h2 class="event-card__title">Performer Lineup</h2>
        <p class="event-card__subtitle">Optional lineup information for speakers, artists, or DJs</p>
      </div>
    </div>
    <button type="button" onclick="addLineupRow()" class="event-add-btn">Add Performer</button>
  </div>

  <div id="lineup-rows" class="event-lineup-list">
    @for($i = 0; $i < $lineupCount; $i++)
    <div class="event-lineup-row lineup-row">
      <div class="event-field">
        <label class="event-label">Performer Name</label>
        <input type="text" name="lineup_names[]" value="{{ $lineupNames[$i] ?? '' }}" maxlength="50" placeholder="Performer name" class="event-input">
      </div>
      <div class="event-field">
        <label class="event-label">Role / Band</label>
        <input type="text" name="lineup_roles[]" value="{{ $lineupRoles[$i] ?? '' }}" maxlength="50" placeholder="Role / DJ / Band" class="event-input">
      </div>
      <div class="event-field">
        <label class="event-label">Time</label>
        <input type="text" name="lineup_times[]" value="{{ $lineupTimes[$i] ?? '' }}" maxlength="5" inputmode="numeric" pattern="(?:[01]\d|2[0-3]):[0-5]\d" placeholder="HH:MM (e.g. 20:00)" title="Time must be in valid HH:MM format (e.g. 20:00)" autocomplete="off" spellcheck="false" class="event-input" data-lineup-time>
      </div>  
      <button type="button" onclick="this.closest('.lineup-row').remove()" class="event-icon-btn" aria-label="Remove performer">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    @endfor
  </div>
</section>

<section class="event-card">
  <div class="event-card__head">
    <div class="event-card__title-wrap">
      <span class="event-card__icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0"/>
          <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.52-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286"/>
          <path d="M7.001 11a1 1 0 1 0 2 0 1 1 0 0 0-2 0"/>
        </svg>
      </span>
      <div>
        <h2 class="event-card__title">Faqs</h2>
        <p class="event-card__subtitle">Optional information to enhance your event listing</p>
      </div>
    </div>
  </div>

  <div class="event-grid">
      <div class="event-field">
        <label class="event-label">Parking / Transport Info <span class="event-inline-note">(optional)</span></label>
        <textarea name="parking_info" rows="3" maxlength="1000" class="event-textarea" placeholder="Nearest tube: North Greenwich. Parking available at...">{{ old('parking_info') }}</textarea>
      </div>
  </div>
</section>


<section class="event-card">
  <div class="event-card__head">
    <div class="event-card__title-wrap">
      <span class="event-card__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M4.75 7.75h14.5M4.75 12h14.5M4.75 16.25h14.5"/></svg>
      </span>
      <div>
        <h2 class="event-card__title">Additional Details</h2>
        <p class="event-card__subtitle">Optional information to enhance your event listing</p>
      </div>
    </div>
  </div>


    <div class="event-field">
      <label class="event-label">Refund Policy <span class="event-inline-note">(optional)</span></label>
      <textarea id="refund_policy" name="refund_policy" rows="3" maxlength="1000" class="event-textarea" placeholder="e.g. Tickets are non-refundable but are transferable up to 48 hours before the event.">{{ old('refund_policy') }}</textarea>
      <div class="event-inline-note">Maximum 1000 characters.</div>
    </div>
</section>

    <div class="event-actions">
      <a href="{{ route('organiser.events.index') }}" class="event-secondary-btn">Cancel</a>
      <button type="submit" class="event-primary-btn">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.75 12.75 10 17l8.25-9.25"/></svg>
        <span>Next - Add Tier</span>
      </button>
    </div>


</form>
</div>
@endsection

@section('scripts')
<script>
const serverErrors = @json($errors->getMessages());
const descriptionField = document.querySelector('#description');
const descriptionMaxLength = Number(descriptionField?.getAttribute('maxlength')) || 0;
const refundPolicyField = document.querySelector('#refund_policy');
const refundPolicyMaxLength = Number(refundPolicyField?.getAttribute('maxlength')) || 0;
const posterAutofillUrl = @json(route('organiser.events.poster.parse'));
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let descriptionEditorInstance = null;
let lastValidDescriptionData = descriptionField?.value || '';
let isApplyingDescriptionLimit = false;
let refundPolicyEditorInstance = null;
let lastValidRefundPolicyData = refundPolicyField?.value || '';
let isApplyingRefundPolicyLimit = false;
let pendingAutofillDescription = null;
let pendingAutofillRefundPolicy = null;
let posterAutofillRequestId = 0;

ClassicEditor.create(descriptionField, {
    toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','blockQuote','undo','redo'],
}).then((editor) => {
    descriptionEditorInstance = editor;
    lastValidDescriptionData = editor.getData();

    if (pendingAutofillDescription !== null) {
        setDescriptionValue(pendingAutofillDescription);
        pendingAutofillDescription = null;
    }

    editor.model.document.on('change:data', () => {
        if (!isApplyingDescriptionLimit) {
            enforceDescriptionMaxlength(descriptionField);
        }
        validateMaxlengthField(descriptionField);
    });
}).catch(console.error);

ClassicEditor.create(refundPolicyField, {
    toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','blockQuote','undo','redo'],
}).then((editor) => {
    refundPolicyEditorInstance = editor;
    lastValidRefundPolicyData = editor.getData();

    if (pendingAutofillRefundPolicy !== null) {
        setRefundPolicyValue(pendingAutofillRefundPolicy);
        pendingAutofillRefundPolicy = null;
    }

    editor.model.document.on('change:data', () => {
        if (!isApplyingRefundPolicyLimit) {
            enforceRefundPolicyMaxlength(refundPolicyField);
        }
        validateMaxlengthField(refundPolicyField);
    });
}).catch(console.error);

function getEditorTextFromHtml(html) {
    const container = document.createElement('div');
    container.innerHTML = html || '';
    return (container.textContent || container.innerText || '').replace(/\u00a0/g, ' ');
}

function truncateEditorHtml(html, maxLength) {
    const source = document.createElement('div');
    const target = document.createElement('div');
    let remaining = maxLength;

    source.innerHTML = html || '';

    function appendTruncatedNode(node, parent) {
        if (node.nodeType === Node.TEXT_NODE) {
            const text = node.textContent || '';
            if (!text || remaining <= 0) {
                return false;
            }

            const chunk = text.slice(0, remaining);
            if (!chunk) {
                return false;
            }

            remaining -= chunk.length;
            parent.appendChild(document.createTextNode(chunk));
            return true;
        }

        if (node.nodeType !== Node.ELEMENT_NODE) {
            return false;
        }

        if (node.tagName === 'BR') {
            if (!parent.childNodes.length) {
                return false;
            }
            parent.appendChild(node.cloneNode(false));
            return true;
        }

        const clone = node.cloneNode(false);
        let hasContent = false;

        Array.from(node.childNodes).forEach((child) => {
            if (remaining <= 0) {
                return;
            }

            if (appendTruncatedNode(child, clone)) {
                hasContent = true;
            }
        });

        if (!hasContent) {
            return false;
        }

        parent.appendChild(clone);
        return true;
    }

    Array.from(source.childNodes).forEach((child) => {
        if (remaining <= 0) {
            return;
        }

        appendTruncatedNode(child, target);
    });

    return target.innerHTML;
}

function moveDescriptionCursorToEnd() {
    if (!descriptionEditorInstance) return;

    descriptionEditorInstance.editing.view.focus();
    descriptionEditorInstance.model.change((writer) => {
        writer.setSelection(descriptionEditorInstance.model.document.getRoot(), 'end');
    });
}

function moveRefundPolicyCursorToEnd() {
    if (!refundPolicyEditorInstance) return;

    refundPolicyEditorInstance.editing.view.focus();
    refundPolicyEditorInstance.model.change((writer) => {
        writer.setSelection(refundPolicyEditorInstance.model.document.getRoot(), 'end');
    });
}

function enforceDescriptionMaxlength(field) {
    if (!field || !descriptionEditorInstance || !descriptionMaxLength) {
        return true;
    }

    const currentData = descriptionEditorInstance.getData();
    if (getEditorTextFromHtml(currentData).length <= descriptionMaxLength) {
        lastValidDescriptionData = currentData;
        return true;
    }

    const truncatedData = truncateEditorHtml(currentData, descriptionMaxLength) || lastValidDescriptionData;

    isApplyingDescriptionLimit = true;
    descriptionEditorInstance.setData(truncatedData);
    lastValidDescriptionData = truncatedData;

    requestAnimationFrame(() => {
        moveDescriptionCursorToEnd();
        isApplyingDescriptionLimit = false;
        validateMaxlengthField(field);
    });

    setFieldError(field, `Maximum ${descriptionMaxLength} characters allowed.`);
    return false;
}

function enforceRefundPolicyMaxlength(field) {
    if (!field || !refundPolicyEditorInstance || !refundPolicyMaxLength) {
        return true;
    }

    const currentData = refundPolicyEditorInstance.getData();
    if (getEditorTextFromHtml(currentData).length <= refundPolicyMaxLength) {
        lastValidRefundPolicyData = currentData;
        return true;
    }

    const truncatedData = truncateEditorHtml(currentData, refundPolicyMaxLength) || lastValidRefundPolicyData;

    isApplyingRefundPolicyLimit = true;
    refundPolicyEditorInstance.setData(truncatedData);
    lastValidRefundPolicyData = truncatedData;

    requestAnimationFrame(() => {
        moveRefundPolicyCursorToEnd();
        isApplyingRefundPolicyLimit = false;
        validateMaxlengthField(field);
    });

    setFieldError(field, `Maximum ${refundPolicyMaxLength} characters allowed.`);
    return false;
}

function openDateTimePicker(eventOrTrigger, maybeTrigger) {
    const event = maybeTrigger ? eventOrTrigger : null;
    const trigger = maybeTrigger || eventOrTrigger;

    event?.preventDefault();
    event?.stopPropagation();

    const input = trigger?.closest('.event-datetime-wrap')?.querySelector('.js-datetime-input');
    if (!input) return;
    if (input._flatpickr) {
        requestAnimationFrame(() => {
            input._flatpickr.altInput?.focus({ preventScroll: true });
            input._flatpickr.open();
        });
        return;
    }

    if (typeof input.showPicker === 'function') {
        input.showPicker();
        return;
    }

    input.focus({ preventScroll: true });
    input.click();
}

function addLineupRow() {
    const row = document.createElement('div');
    row.className = 'event-lineup-row lineup-row';
    row.innerHTML = `
        <div class="event-field">
            <label class="event-label">Performer Name</label>
            <input type="text" name="lineup_names[]" maxlength="50" placeholder="Performer name" class="event-input">
        </div>
        <div class="event-field">
            <label class="event-label">Role / Band</label>
            <input type="text" name="lineup_roles[]" maxlength="50" placeholder="Role / DJ / Band" class="event-input">
        </div>
        <div class="event-field">
            <label class="event-label">Time</label>
            <input type="text" name="lineup_times[]" maxlength="5" inputmode="numeric" pattern="(?:[01]\d|2[0-3]):[0-5]\d" placeholder="HH:MM (e.g. 20:00)" title="Time must be in valid HH:MM format (e.g. 20:00)" autocomplete="off" spellcheck="false" class="event-input" data-lineup-time>
        </div>
        <button type="button" onclick="this.closest('.lineup-row').remove()" class="event-icon-btn" aria-label="Remove performer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    `;
    row.querySelectorAll('input[maxlength], textarea[maxlength]').forEach((field) => {
        setupMaxlengthValidation(field);
    });
    setupLineupTimeField(row.querySelector('[data-lineup-time]'));
    document.getElementById('lineup-rows').appendChild(row);
}

function parseLocalDateTime(value) {
    if (!value || !value.includes('T')) return null;
    const [datePart, timePart] = value.split('T');
    if (!datePart || !timePart) return null;
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour, minute] = timePart.split(':').map(Number);
    if ([year, month, day, hour, minute].some(Number.isNaN)) return null;
    return new Date(year, month - 1, day, hour, minute, 0, 0);
}

function formatLocalDateTime(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hour = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hour}:${minute}`;
}

function formatDateTimeDisplay(value) {
    const date = parseLocalDateTime(value);
    if (!date) return '';

    const formattedDate = new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
    const formattedTime = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date).replace(/\s+/g, '').toLowerCase();

    return `${formattedDate} ${formattedTime}`;
}

function syncValidationTimes(force = false) {
    const startsAtInput = document.querySelector('input[name="starts_at"]');
    const endsAtInput = document.querySelector('input[name="ends_at"]');
    const validationStartInput = document.querySelector('input[name="ticket_validation_starts_at"]');
    const validationEndInput = document.querySelector('input[name="ticket_validation_ends_at"]');
    if (!startsAtInput || !endsAtInput || !validationStartInput || !validationEndInput) return;

    const startsAt = parseLocalDateTime(startsAtInput.value);
    const endsAt = parseLocalDateTime(endsAtInput.value);

    if (startsAt && (force || validationStartInput.dataset.autoManaged === 'true')) {
        const validationStart = new Date(startsAt.getTime() - (2 * 60 * 60 * 1000));
        const formattedValue = formatLocalDateTime(validationStart);
        if (validationStartInput._flatpickr) {
            validationStartInput._flatpickr.setDate(formattedValue, true, 'Y-m-d\\TH:i');
        } else {
            validationStartInput.value = formattedValue;
            validationStartInput._syncDisplay?.();
        }
    }

    if (endsAt && (force || validationEndInput.dataset.autoManaged === 'true')) {
        const formattedValue = formatLocalDateTime(endsAt);
        if (validationEndInput._flatpickr) {
            validationEndInput._flatpickr.setDate(formattedValue, true, 'Y-m-d\\TH:i');
        } else {
            validationEndInput.value = formattedValue;
            validationEndInput._syncDisplay?.();
        }
    }
}

const lineupTimePattern = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
const lineupTimeErrorMessage = 'Time must be in valid HH:MM format (e.g. 20:00)';

function formatLineupTimeValue(value) {
    const digits = value.replace(/\D/g, '').slice(0, 4);
    if (digits.length <= 2) {
        return digits;
    }

    return `${digits.slice(0, 2)}:${digits.slice(2)}`;
}

function isLineupTimeControlKey(event) {
    return [
        'Backspace',
        'Delete',
        'Tab',
        'ArrowLeft',
        'ArrowRight',
        'ArrowUp',
        'ArrowDown',
        'Home',
        'End',
    ].includes(event.key);
}

function handleLineupTimeKeydown(event) {
    if (event.ctrlKey || event.metaKey || event.altKey || isLineupTimeControlKey(event)) {
        return;
    }

    if (/^\d$/.test(event.key)) {
        return;
    }

    if (event.key === ':') {
        const field = event.currentTarget;
        const digitsCount = field.value.replace(/\D/g, '').length;

        if (!field.value.includes(':') && digitsCount >= 2) {
            return;
        }
    }

    event.preventDefault();
}

function validateLineupTimeField(field) {
    if (!field) return true;

    const value = field.value.trim();
    if (value === '') {
        field.setCustomValidity('');
        const existingText = field.parentElement?.querySelector('.event-field-error')?.textContent || '';
        if (existingText === lineupTimeErrorMessage) {
            setFieldError(field, '');
        }
        return true;
    }

    const isValid = lineupTimePattern.test(value);
    field.setCustomValidity(isValid ? '' : lineupTimeErrorMessage);
    setFieldError(field, isValid ? '' : lineupTimeErrorMessage);

    return isValid;
}

function handleLineupTimeInput(event) {
    const field = event.currentTarget;
    field.value = formatLineupTimeValue(field.value);
    validateLineupTimeField(field);
}

function setupLineupTimeField(field) {
    if (!field || field.dataset.timeFieldReady === 'true') {
        return;
    }

    field.dataset.timeFieldReady = 'true';
    field.value = formatLineupTimeValue(field.value);
    field.addEventListener('keydown', handleLineupTimeKeydown);
    field.addEventListener('input', handleLineupTimeInput);
    field.addEventListener('blur', () => validateLineupTimeField(field));
    field.addEventListener('change', () => validateLineupTimeField(field));
    field.addEventListener('paste', () => {
        requestAnimationFrame(() => {
            field.value = formatLineupTimeValue(field.value);
            validateLineupTimeField(field);
        });
    });
}

function setupNativeDateTimeFallback(input) {
    const wrap = input.closest('.event-datetime-wrap');
    input.type = 'datetime-local';
    input.removeAttribute('step');
    input.classList.add('event-input--native-datetime');
    wrap?.classList.add('event-datetime-wrap--native');

    let displayInput = wrap?.querySelector('.js-datetime-display');
    if (!displayInput && wrap) {
        displayInput = document.createElement('input');
        displayInput.type = 'text';
        displayInput.readOnly = true;
        displayInput.className = 'event-input event-input--datetime-display js-datetime-display';
        displayInput.placeholder = 'Select date and time';
        input.insertAdjacentElement('beforebegin', displayInput);
    }
    if (displayInput && !displayInput.placeholder) {
        displayInput.placeholder = 'Select date and time';
    }

    const syncDisplay = () => {
        if (!displayInput) return;
        displayInput.value = formatDateTimeDisplay(input.value);
    };

    input._syncDisplay = syncDisplay;
    syncDisplay();
    input.addEventListener('change', syncDisplay);
    input.addEventListener('input', syncDisplay);

    displayInput?.addEventListener('click', () => openDateTimePicker(displayInput));
    displayInput?.addEventListener('focus', () => openDateTimePicker(displayInput));
}

document.querySelectorAll('.js-datetime-input').forEach((input) => {
    setupNativeDateTimeFallback(input);
});

document.querySelectorAll('[data-lineup-time]').forEach((field) => {
    setupLineupTimeField(field);
});

function getFieldByErrorKey(fieldKey) {
    const directField = document.querySelector(`[name="${fieldKey}"]`);
    if (directField) return directField;

    const indexedMatch = fieldKey.match(/^([a-zA-Z0-9_]+)\.(\d+)$/);
    if (!indexedMatch) return null;

    const [, baseName, index] = indexedMatch;
    const fields = document.querySelectorAll(`[name="${baseName}[]"]`);
    return fields[Number(index)] || null;
}

function ensureFieldErrorElement(field) {
    if (!field) return null;
    const existing = field.parentElement?.querySelector('.event-field-error');
    if (existing) return existing;

    const errorEl = document.createElement('p');
    errorEl.className = 'event-field-error';
    const editor = ['description', 'refund_policy'].includes(field.name) ? field.parentElement?.querySelector('.ck-editor') : null;
    if (editor) {
        editor.insertAdjacentElement('afterend', errorEl);
    } else {
        field.insertAdjacentElement('afterend', errorEl);
    }
    return errorEl;
}

function setFieldError(field, message) {
    const errorEl = ensureFieldErrorElement(field);
    if (!errorEl) return;
    const highlightTarget = ['description', 'refund_policy'].includes(field.name)
        ? field.parentElement?.querySelector('.ck-editor__editable') || field
        : field;

    if (message) {
        errorEl.textContent = message;
        errorEl.classList.add('is-visible');
        highlightTarget.classList.add('border-red-500');
    } else {
        errorEl.textContent = '';
        errorEl.classList.remove('is-visible');
        highlightTarget.classList.remove('border-red-500');
    }
}

function triggerFieldUpdate(field) {
    if (!field) return;

    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
}

function setInputValue(selector, value) {
    if (typeof value !== 'string') {
        return;
    }

    const field = document.querySelector(selector);
    if (!field) {
        return;
    }

    field.value = value.trim();
    triggerFieldUpdate(field);
}

function escapeHtml(value) {
    const container = document.createElement('div');
    container.textContent = value;
    return container.innerHTML;
}

function textToEditorHtml(value) {
    return value
        .split(/\n+/)
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => `<p>${escapeHtml(line)}</p>`)
        .join('');
}

function setDescriptionValue(value) {
    if (typeof value !== 'string' || !descriptionField) {
        return;
    }

    const trimmedValue = value.trim();

    if (!descriptionEditorInstance) {
        pendingAutofillDescription = trimmedValue;
        descriptionField.value = trimmedValue;
        validateMaxlengthField(descriptionField);
        return;
    }

    descriptionEditorInstance.setData(textToEditorHtml(trimmedValue));
    descriptionField.value = trimmedValue;
    lastValidDescriptionData = descriptionEditorInstance.getData();
    validateMaxlengthField(descriptionField);
}

function setRefundPolicyValue(value) {
    if (typeof value !== 'string' || !refundPolicyField) {
        return;
    }

    const trimmedValue = value.trim();
    if (!trimmedValue) {
        return;
    }

    if (!refundPolicyEditorInstance) {
        pendingAutofillRefundPolicy = trimmedValue;
        refundPolicyField.value = trimmedValue;
        validateMaxlengthField(refundPolicyField);
        return;
    }

    refundPolicyEditorInstance.setData(textToEditorHtml(trimmedValue));
    refundPolicyField.value = trimmedValue;
    lastValidRefundPolicyData = refundPolicyEditorInstance.getData();
    validateMaxlengthField(refundPolicyField);
}

function setSelectValue(selector, value) {
    if (typeof value !== 'string') {
        return;
    }

    const normalizedValue = value.trim().toLowerCase();
    if (!normalizedValue) {
        return;
    }

    const field = document.querySelector(selector);
    if (!(field instanceof HTMLSelectElement)) {
        return;
    }

    const matchedOption = Array.from(field.options).find((option) => {
        return option.value.trim().toLowerCase() === normalizedValue
            || option.textContent.trim().toLowerCase() === normalizedValue;
    });

    if (!matchedOption) {
        return;
    }

    field.value = matchedOption.value;
    triggerFieldUpdate(field);
}

function updateSelectedFileName(input, text) {
    const scope = input.closest('.js-file-upload-scope');
    const selector = input.dataset.fileNameSelector || '.js-file-name';
    const fileNameEl = scope?.querySelector(selector);

    if (!fileNameEl) {
        return;
    }

    fileNameEl.textContent = text;
}

function applyPosterAutofill(payload) {
    if (!payload || typeof payload !== 'object') {
        return;
    }

    setInputValue('input[name="title"]', payload.event_title);
    setInputValue('input[name="short_description"]', payload.short_description);
    setDescriptionValue(payload.full_description);
    setInputValue('input[name="starts_at"]', payload.start_datetime);
    setInputValue('input[name="ends_at"]', payload.end_datetime);

    if (validationStartInput && validationEndInput) {
        validationStartInput.dataset.autoManaged = 'true';
        validationEndInput.dataset.autoManaged = 'true';
        syncValidationTimes(true);
    }

    setInputValue('input[name="venue_name"]', payload.venue_name);
    setInputValue('input[name="city"]', payload.city);
    setInputValue('input[name="venue_address"]', payload.address);
    setInputValue('input[name="postcode"]', payload.postcode);
    setSelectValue('select[name="category"]', payload.category);
    setInputValue(
        'textarea[name="parking_info"]',
        typeof payload.parking_transport_info === 'string' ? payload.parking_transport_info : payload.parking_info
    );
    setRefundPolicyValue(payload.refund_policy);
}

async function requestPosterAutofill(file) {
    const formData = new FormData();
    formData.append('poster', file);

    const response = await fetch(posterAutofillUrl, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
        body: formData,
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok || !payload) {
        throw new Error(payload?.message || 'Poster autofill failed.');
    }

    return payload;
}

document.querySelectorAll('.js-banner-input').forEach((input) => {
    input.addEventListener('change', function () {
        const file = this.files && this.files[0] ? this.files[0] : null;
        updateSelectedFileName(this, file ? file.name : 'No file selected');
    });
});

document.querySelectorAll('.js-ai-image-input').forEach((input) => {
    input.addEventListener('change', async function () {
        const file = this.files && this.files[0] ? this.files[0] : null;

        updateSelectedFileName(this, file ? `${file.name} - extracting details...` : 'No file selected');

        if (!file) {
            return;
        }

        const requestId = ++posterAutofillRequestId;

        try {
            const payload = await requestPosterAutofill(file);

            if (requestId !== posterAutofillRequestId) {
                return;
            }

            applyPosterAutofill(payload);
            updateSelectedFileName(this, file.name);
        } catch (error) {
            if (requestId !== posterAutofillRequestId) {
                return;
            }

            updateSelectedFileName(this, file.name);
            console.error('Poster autofill failed.', error);
        }
    });
});

function getPlainEditorText() {
    if (!descriptionEditorInstance) return descriptionField?.value || '';
    return getEditorTextFromHtml(descriptionEditorInstance.getData());
}

function getRefundPolicyPlainText() {
    if (!refundPolicyEditorInstance) return refundPolicyField?.value || '';
    return getEditorTextFromHtml(refundPolicyEditorInstance.getData());
}

function validateMaxlengthField(field) {
    if (field.matches('[data-lineup-time]')) {
        return validateLineupTimeField(field);
    }

    const maxLength = Number(field.getAttribute('maxlength'));
    if (!maxLength) return true;

    const isDescription = field.name === 'description';
    const isRefundPolicy = field.name === 'refund_policy';
    const valueLength = isDescription
        ? getPlainEditorText().length
        : (isRefundPolicy ? getRefundPolicyPlainText().length : field.value.length);
    if (valueLength > maxLength) {
        setFieldError(field, `Maximum ${maxLength} characters allowed.`);
        return false;
    }

    const fieldLabel = (field.closest('.event-field')?.querySelector('.event-label')?.textContent || field.name || 'This field')
        .replace(/\*/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    if (valueLength === maxLength) {
        setFieldError(field, `${fieldLabel} maximum limit reached.`);
        return true;
    }

    if (valueLength > maxLength) {
        setFieldError(field, `${fieldLabel} maximum limit reached.`);
        return false;
    }

    const existingText = field.parentElement?.querySelector('.event-field-error')?.textContent || '';
    if (existingText === `${fieldLabel} maximum limit reached.` || existingText === `Maximum ${maxLength} characters allowed.`) {
        setFieldError(field, '');
    }
    return true;
}

function setupMaxlengthValidation(field) {
    if (!field || field.dataset.maxlengthValidationReady === 'true') {
        return;
    }

    field.dataset.maxlengthValidationReady = 'true';
    field.addEventListener('input', () => validateMaxlengthField(field));
    field.addEventListener('blur', () => validateMaxlengthField(field));
}

document.querySelectorAll('#event-form input[maxlength], #event-form textarea[maxlength]').forEach((field) => {
    setupMaxlengthValidation(field);
});

Object.entries(serverErrors).forEach(([fieldKey, messages]) => {
    const field = getFieldByErrorKey(fieldKey);
    if (!field || !messages.length) return;
    setFieldError(field, messages[0]);
});

document.getElementById('event-form')?.addEventListener('submit', function (event) {
    const fields = this.querySelectorAll('input[maxlength], textarea[maxlength]');
    const lineupTimeFields = this.querySelectorAll('[data-lineup-time]');
    let hasValidationError = false;

    fields.forEach((field) => {
        if (!validateMaxlengthField(field)) {
            hasValidationError = true;
        }
    });

    lineupTimeFields.forEach((field) => {
        if (!validateLineupTimeField(field)) {
            hasValidationError = true;
        }
    });

    if (hasValidationError) {
        event.preventDefault();
    }
});

const startsAtInput = document.querySelector('input[name="starts_at"]');
const endsAtInput = document.querySelector('input[name="ends_at"]');
const validationStartInput = document.querySelector('input[name="ticket_validation_starts_at"]');
const validationEndInput = document.querySelector('input[name="ticket_validation_ends_at"]');

if (startsAtInput && endsAtInput && validationStartInput && validationEndInput) {
    const startsAt = parseLocalDateTime(startsAtInput.value);
    const endsAt = parseLocalDateTime(endsAtInput.value);
    const defaultStartValue = startsAt ? formatLocalDateTime(new Date(startsAt.getTime() - (2 * 60 * 60 * 1000))) : '';
    const defaultEndValue = endsAt ? formatLocalDateTime(endsAt) : '';

    validationStartInput.dataset.autoManaged = (!validationStartInput.value || validationStartInput.value === defaultStartValue) ? 'true' : 'false';
    validationEndInput.dataset.autoManaged = (!validationEndInput.value || validationEndInput.value === defaultEndValue) ? 'true' : 'false';

    syncValidationTimes();

    startsAtInput.addEventListener('change', () => syncValidationTimes());
    startsAtInput.addEventListener('input', () => syncValidationTimes());
    endsAtInput.addEventListener('change', () => syncValidationTimes());
    endsAtInput.addEventListener('input', () => syncValidationTimes());

    validationStartInput.addEventListener('change', () => {
        validationStartInput.dataset.autoManaged = 'false';
    });
    validationStartInput.addEventListener('input', () => {
        validationStartInput.dataset.autoManaged = 'false';
    });
    validationEndInput.addEventListener('change', () => {
        validationEndInput.dataset.autoManaged = 'false';
    });
    validationEndInput.addEventListener('input', () => {
        validationEndInput.dataset.autoManaged = 'false';
    });
}
</script>
@endsection
