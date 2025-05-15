<script setup>
import { ref, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import SocialLinks from "./SocialLinks.vue";
import NavLinks from "./NavLinks.vue";
import CopyrightBar from "./CopyrightBar.vue";
import PromoFooterBanner from "./PromoFooterBanner.vue";
import CosmicStarfield from "@/Components/User/Navigation/CosmicStarfield.vue";

// We'll simulate getting this from the backend in a real implementation
// In a production app, this would come from props passed from the layout
const page = usePage();
const judulWeb = computed(() => page.props.web_details.judul_web);

const footerData = {
    logo_footer: page.props.web_details.logo_footer,
    meta_description: page.props.web_details.meta_description,
    support_instagram: page.props.web_details.support_instagram,
    support_whatsapp: page.props.web_details.support_whatsapp,
    support_email: page.props.web_details.support_email,
    support_youtube: page.props.web_details.support_youtube,
    support_facebook: page.props.web_details.support_facebook,
};

const isPreludeVisible = ref(false);
const footerRef = ref(null);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            isPreludeVisible.value = entries[0].isIntersecting;
        },
        { threshold: 0.1 }
    );

    if (footerRef.value) {
        observer.observe(footerRef.value);
    }
});

const navLinks = [
    { name: "Topup", route: "index" },
    { name: "Transactions", route: "index" },
    { name: "Leaderboard", route: "index" },
    { name: "Calculator", route: "index" },
];

const legalLinks = [
    { name: "Terms of Service", route: "index" },
    { name: "Privacy Policy", route: "index" },
    { name: "Refund Policy", route: "index" },
];

// Compute classes based on visibility for smooth transitions
// const preludeClasses = computed(() => {
//     return {
//         "opacity-100": isPreludeVisible.value,
//         "opacity-0": !isPreludeVisible.value,
//         "transform translate-y-0": isPreludeVisible.value,
//         "transform translate-y-10": !isPreludeVisible.value,
//     };
// });
</script>

<template>
    <footer ref="footerRef" class="relative pt-0">
        <!-- Cosmic Prelude with Earth and Rocket -->

        <!-- Promotional Banner -->
        <PromoFooterBanner />

        <!-- Main Footer Content -->
        <div
            class="relative pt-12 pb-8 overflow-hidden bg-gradient-to-b to-footer_background via-footer_background/50 from-content_background/50"
        >
            <div class="absolute inset-0 overflow-hidden">
                <CosmicStarfield class="opacity-50" />
            </div>
            <!-- Cosmic background elements -->
            <!-- <div class="absolute inset-0 opacity-50">
            <div
            class="absolute w-1 h-1 bg-white rounded-full top-10 left-1/4"
            ></div>
            <div
            class="absolute top-20 left-1/3 w-1.5 h-1.5 bg-white rounded-full"
            ></div>
            <div
            class="absolute w-1 h-1 bg-white rounded-full bottom-10 right-1/4"
            ></div>
            <div
            class="absolute w-2 h-2 bg-white rounded-full bottom-20 right-1/3"
            ></div>
            <div
            class="absolute w-1 h-1 bg-white rounded-full top-1/3 left-2/3"
            ></div>
            <div
            class="absolute top-2/3 left-1/2 w-0.5 h-0.5 bg-white rounded-full"
            ></div>
            <div
            class="absolute w-1 h-1 bg-white rounded-full top-1/2 right-1/3"
            ></div>
        </div> -->

            <div class="relative z-10 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-12"
                >
                    <!-- Brand Column -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <img
                                :src="footerData.logo_footer"
                                alt="Logo"
                                class="w-8 h-8"
                            />
                            <span class="text-xl font-bold text-primary-text">{{
                                judulWeb
                            }}</span>
                        </div>

                        <p class="text-sm text-primary-text/70">
                            {{ footerData.meta_description }}
                        </p>

                        <!-- Social Links -->
                        <SocialLinks :social-data="footerData" />
                    </div>

                    <!-- Quick Links Column -->
                    <div>
                        <h3
                            class="mb-4 text-lg font-semibold text-primary-text"
                        >
                            Quick Links
                        </h3>
                        <NavLinks :links="navLinks" />
                    </div>

                    <!-- Legal Links Column -->
                    <div>
                        <h3
                            class="mb-4 text-lg font-semibold text-primary-text"
                        >
                            Legal
                        </h3>
                        <NavLinks :links="legalLinks" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright Bar -->
        <CopyrightBar :judulWeb="judulWeb" />
    </footer>
</template>
