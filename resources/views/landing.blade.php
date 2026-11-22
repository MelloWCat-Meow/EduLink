<x-layouts.app>
    <x-landing.navbar />

    <main id="konten">
        <x-landing.hero :subjects="$subjects" :levels="$levels" :locations="$locations" />
        <x-landing.how-it-works :steps="$steps" />
        <x-landing.learning-modes />
        <x-landing.sample-tutors :tutors="$sampleTutors" :levels="$levels" />
        <x-landing.verification />
        <x-landing.why-edulink />
        <x-landing.subjects :levels="$levels" />
        <x-landing.for-tutors />
        <x-landing.faq :faqs="$faqs" />
        <x-landing.closing-cta />
    </main>

    <x-landing.footer :contact="$contact" />
</x-layouts.app>
