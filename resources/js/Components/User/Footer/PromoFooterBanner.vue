<script setup>
// Import necessary dependencies
import CosmicParticles from "../Flashsale/CosmicParticles.vue";
</script>

<template>
    <div class="relative w-full h-auto overflow-hidden bg-transparent">
        <!-- Overlay gradient to help text contrast -->
        <!-- <div
            class="absolute inset-0 z-10 bg-gradient-to-r from-dark via-transparent to-dark"
        ></div> -->

        <!-- Cosmic particles overlay -->
        <CosmicParticles class="absolute inset-0" />
        <!-- Cosmic noise texture overlay -->
        <div class="absolute inset-0 z-20 opacity-5">
            <svg width="100%" height="100%">
                <filter id="noise">
                    <feTurbulence
                        type="fractalNoise"
                        baseFrequency="0.65"
                        numOctaves="3"
                        stitchTiles="stitch"
                    />
                    <feColorMatrix type="saturate" values="0" />
                </filter>
                <rect
                    width="100%"
                    height="100%"
                    filter="url(#noise)"
                    opacity="0.5"
                />
            </svg>
        </div>

        <!-- Banner image -->
        <img
            src="/img/footerBanner/bg-footer.webp"
            alt="Cosmic Promotions"
            class="object-cover w-full h-auto"
            loading="lazy"
            fetchpriority="low"
        />

        <!-- Text overlay -->
        <!-- <div class="absolute inset-0 z-30 flex items-center">
            <div class="w-full px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-lg">
                    <h3
                        class="mb-2 text-2xl font-bold text-primary-text sm:text-3xl md:text-4xl"
                    >
                        Cosmic Promotions
                    </h3>
                    <p class="mb-6 text-primary-text/80">
                        Get special deals across multiple top-up options
                    </p>
                    <a
                        href="#"
                        class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-primary-text rounded-full inline-flex items-center transition-all duration-200 transform hover:translate-x-1"
                    >
                        Explore Offers
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 ml-2"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </a>
                </div>
            </div>
        </div> -->
    </div>
</template>
