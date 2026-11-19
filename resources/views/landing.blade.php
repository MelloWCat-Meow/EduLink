<x-layouts.app>
    <x-landing.navbar />

    <main id="konten">
        <x-landing.hero :subjects="$subjects" :levels="$levels" :locations="$locations" />
        <x-landing.subjects :levels="$levels" />
        <x-landing.how-it-works />
        <x-landing.learning-modes />
        <x-landing.sample-tutors :tutors="$sampleTutors" />
        <x-landing.why-edulink />
        <x-landing.for-tutors />
        <x-landing.faq />
        <x-landing.closing-cta />
    </main>

    <x-landing.footer :contact="$contact" />
</x-layouts.app>
