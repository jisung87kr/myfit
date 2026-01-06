@extends('layouts.app')

@section('title', '초기 설문 - MyFit')

@push('styles')
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
/* Disable number input spinner */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
@endpush

@section('content')
<div id="survey-app"></div>
@endsection

@push('scripts')
<script type="module">
import SurveyComponent from '/js/components/SurveyComponent.js';

const app = Vue.createApp({
    components: {
        'survey-component': SurveyComponent
    },
    template: `
        <survey-component
            api-base-url="/api"
            redirect-url="{{ route('diet-plan.index') }}"
            auth-token="{{ session('auth_token', '') }}"
        />
    `
});

app.mount('#survey-app');
</script>
@endpush
