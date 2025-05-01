<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CosmicParticles from "@/Components/User/Flashsale/CosmicParticles.vue";
import ProductBanner from "@/Components/Order/ProductBanner.vue";
import ProductInfoPanel from "@/Components/Order/ProductInfoPanel.vue";
import UserDataCard from "@/Components/Order/UserDataCard.vue";
import ServiceList from "@/Components/Order/ServiceList.vue";
import QuantitySelector from "@/Components/Order/QuantitySelector.vue";
import CheckoutSummary from "@/Components/Order/CheckoutSummary.vue";
import HelpContact from "@/Components/Order/HelpContact.vue";
import PaymentSelector from "@/Components/Order/PaymentSelector.vue";
import ContactForm from "@/Components/Order/ContactForm.vue";
import VoucherSection from "@/Components/Order/VoucherSection.vue";
import ProductDescription from "@/Components/Order/ProductDescription.vue";
import FaqSection from "@/Components/Order/FaqSection.vue";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    produk: Object,
    layanans: Array,
    flashsaleItems: Array,
    user: Object,
    inputFields: Array,
    waNumber: String,
    flashsaleEvents: Array,
    staticMethods: Array,
    dynamicMethods: Array,
    activeVouchers: Array,
    faqs: Array,
});

const selectedService = ref(null);
const quantity = ref(1);
const { toast } = useToast();
const sidebarRef = ref(null);
const isSidebarSticky = ref(false);
const footerVisible = ref(false);
const navbarHeight = ref(64);
const orderQuantityRef = ref(null);
const contactSectionRef = ref(null);
const paymentInfo = ref(null);
const selectedPayment = ref(null);
const contactData = ref({ email: "", phone: "", country: "ID" });
const selectedVoucher = ref(null);
const paymentSectionHighlight = ref(false);

const totalAmount = computed(() => {
    if (!selectedService.value) return 0;

    const baseAmount = selectedService.value.harga_jual * quantity.value;
    return baseAmount;
});

const handleServiceSelection = (service) => {
    selectedService.value = service;

    // Auto-scroll to payment section with delay to ensure rendering
    nextTick(() => {
        setTimeout(() => {
            scrollToPaymentSection();
        }, 100);
    });
};

// Scroll to payment section function
const scrollToPaymentSection = () => {
    if (!orderQuantityRef.value) return;

    const isInView = isElementInViewport(orderQuantityRef.value);
    if (isInView) return; // Don't scroll if already in view

    // Smooth scroll to payment section with offset
    const offset = 150; // 32px above the component
    const top =
        orderQuantityRef.value.getBoundingClientRect().top +
        window.pageYOffset -
        offset;

    window.scrollTo({
        top,
        behavior: "smooth",
    });

    // Highlight payment section with pulsing glow
    highlightPaymentSection();
};

// Check if element is in viewport
const isElementInViewport = (el) => {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <=
            (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <=
            (window.innerWidth || document.documentElement.clientWidth)
    );
};

// Highlight payment section with pulsing glow
const highlightPaymentSection = () => {
    if (!orderQuantityRef.value) return;

    paymentSectionHighlight.value = true;

    // Remove highlight after animation (3 pulses ≈ 1500ms)
    setTimeout(() => {
        paymentSectionHighlight.value = false;
    }, 1500);
};

const handleQuantityUpdate = (newQuantity) => {
    quantity.value = newQuantity;
};

const handlePaymentChange = (pay) => {
    selectedPayment.value = pay;
};

const handleFeeChange = (info) => {
    paymentInfo.value = info;
};

const handleContactUpdate = (contact) => {
    contactData.value = contact;
};

const handleVoucherUpdate = (voucher) => {
    selectedVoucher.value = voucher;
};

const handleCheckout = () => {
    if (!selectedService.value) {
        toast.error("Please select a service first");
        return;
    }
    if (!selectedPayment.value) {
        toast.error("Please select a payment method");
        return;
    }
    if (!contactData.value.phone || contactData.value.phone.length < 7) {
        toast.error("Please enter a valid WhatsApp number");
        return;
    }
    toast.success(
        `Processing ${quantity.value} x ${
            selectedService.value.nama_layanan
        } with ${paymentInfo.value?.methodLabel ?? "payment"}`
    );
    contactData.value = { email: "", phone: "", country: "ID" };
    selectedPayment.value = null;
    paymentInfo.value = null;
};

onMounted(() => {
    initPriceAnimations();
});

const initPriceAnimations = () => {
    const animateDigits = (element, targetValue) => {
        if (!element || !targetValue) return;

        const targetString = targetValue.toString();
        const digitContainers = [];

        element.textContent = "";

        for (let i = 0; i < targetString.length; i++) {
            const digitContainer = document.createElement("span");
            digitContainer.className =
                "inline-block overflow-hidden relative w-[0.6em] h-[1em]";

            const digit = document.createElement("span");
            digit.className =
                "absolute transition-transform duration-800 ease-out-back";
            digit.textContent = targetString[i];

            digit.style.transform = "translateY(-1000%)";

            digitContainer.appendChild(digit);
            element.appendChild(digitContainer);

            digitContainers.push({
                container: digitContainer,
                digit: digit,
                targetValue: targetString[i],
            });
        }

        digitContainers.forEach((container, index) => {
            setTimeout(() => {
                container.digit.style.transform = "translateY(0)";
            }, index * 80);
        });
    };

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === "childList") {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        const priceElements =
                            node.querySelectorAll(".flashsale-price");
                        let animationCount = 0;
                        priceElements.forEach((el) => {
                            if (animationCount < 3) {
                                const value = el.dataset.value;
                                if (value) {
                                    animateDigits(el, value);
                                    animationCount++;
                                }
                            }
                        });
                    }
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    let animationCount = 0;
    document.querySelectorAll(".flashsale-price").forEach((el) => {
        if (animationCount < 3) {
            const value = el.dataset.value;
            if (value) {
                animateDigits(el, value);
                animationCount++;
            }
        }
    });
};
</script>

<template>
    <GuestLayout>
        <section>
            <div class="relative">
                <ProductBanner :banner="produk.banner" />
            </div>

            <ProductInfoPanel :produk="produk" :min-price="0" />
        </section>

        <section class="relative px-4 py-8 md:pt-20 bg-content_background">
            <div class="absolute inset-0 z-0">
                <CosmicParticles />
            </div>
            <div
                class="relative z-10 grid grid-cols-1 mx-auto max-w-7xl lg:grid-cols-6 lg:gap-6"
            >
                <div class="lg:col-span-4 lg:pr-8">
                    <UserDataCard
                        :input-fields="inputFields"
                        :produk="produk"
                    />
                    <ServiceList
                        :services="layanans"
                        :flashsale-items="flashsaleItems"
                        :flashsale-events="flashsaleEvents"
                        @select-service="handleServiceSelection"
                    />
                    <div
                        ref="orderQuantityRef"
                        :class="{
                            'cosmic-highlight-pulse': paymentSectionHighlight,
                        }"
                    >
                        <QuantitySelector
                            :disabled="!selectedService"
                            :max-quantity="1000"
                            :initial-quantity="quantity"
                            @update:quantity="handleQuantityUpdate"
                        />
                    </div>
                    <!-- Payment section with ref for scrolling and highlighting -->

                    <PaymentSelector
                        :static-methods="staticMethods"
                        :dynamic-methods="dynamicMethods"
                        :selected-service="selectedService"
                        :selected-payment="selectedPayment"
                        :base-price="
                            selectedService ? selectedService.harga_jual : 0
                        "
                        :quantity="quantity"
                        :voucher="selectedVoucher"
                        @update:selectedPayment="handlePaymentChange"
                        @update:fee="handleFeeChange"
                    />

                    <VoucherSection
                        :active-vouchers="activeVouchers"
                        :total-amount="totalAmount"
                        :selected-service="selectedService"
                        @update:voucher="handleVoucherUpdate"
                    />
                    <div ref="contactSectionRef">
                        <ContactForm
                            :initial-email="contactData.email"
                            :initial-phone="contactData.phone"
                            :initial-country="contactData.country"
                            @update:contact="handleContactUpdate"
                        />
                    </div>
                </div>

                <div class="space-y-4 lg:col-span-2">
                    <div
                        ref="sidebarRef"
                        class="sticky space-y-4 transition-all duration-300 top-36"
                    >
                        <HelpContact :wa-number="waNumber" />
                        <CheckoutSummary
                            :produk="produk"
                            min-price="0"
                            :selected-service="selectedService"
                            :quantity="quantity"
                            :selected-payment="selectedPayment"
                            :payment-info="paymentInfo"
                            :contact="contactData"
                            :voucher="selectedVoucher"
                            @checkout="handleCheckout"
                        />
                    </div>
                </div>
            </div>

            <!-- Product Description Section - Full Width -->
            <div class="relative z-10 mx-auto mt-8 max-w-7xl">
                <ProductDescription :description="produk.deskripsi_game" />

                <FaqSection :faqs="faqs" />
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
.cosmic-sticky {
    transition: all 0.3s ease;
    will-change: transform;
    transform: translateZ(0);
}

.cosmic-sticky::before {
    content: "";
    position: absolute;
    inset: -5px;
    border-radius: 12px;
    background: linear-gradient(
        120deg,
        rgba(51, 195, 240, 0.1),
        rgba(155, 135, 245, 0.1)
    );
    opacity: 0;
    z-index: -1;
    transition: opacity 0.3s ease;
    animation: cosmic-warp 10s infinite alternate;
}

.cosmic-sticky:hover::before {
    opacity: 1;
}

.cosmic-sticky::after {
    content: "";
    position: absolute;
    right: -15px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: radial-gradient(
        circle,
        rgba(51, 195, 240, 0.3) 0%,
        transparent 70%
    );
    opacity: 0.2;
    z-index: -1;
    animation: micro-planet-trail 5s infinite ease-in-out;
}

/* New highlight pulse animation for payment section */
.cosmic-highlight-pulse {
    position: relative;
}

.cosmic-highlight-pulse::before {
    content: "";
    position: absolute;
    inset: -2px;
    border-radius: 8px;
    background: linear-gradient(
        120deg,
        rgba(155, 135, 245, 0.2),
        rgba(51, 195, 240, 0.3)
    );
    z-index: -1;
    animation: payment-highlight-pulse 1.5s ease-in-out;
}

@keyframes payment-highlight-pulse {
    0%,
    100% {
        opacity: 0;
        transform: scale(0.98);
    }
    25%,
    75% {
        opacity: 0.8;
        transform: scale(1.01);
    }
    50% {
        opacity: 0.5;
        transform: scale(1);
    }
}

@keyframes digit-shuffle {
    0% {
        transform: translateY(-1000%);
    }
    70% {
        transform: translateY(10%);
    }
    85% {
        transform: translateY(-5%);
    }
    100% {
        transform: translateY(0);
    }
}

@keyframes cosmic-warp {
    0% {
        background-position: 0% 0%;
    }
    100% {
        background-position: 100% 100%;
    }
}

@keyframes micro-planet-trail {
    0%,
    100% {
        opacity: 0.2;
        height: 30%;
    }
    50% {
        opacity: 0.5;
        height: 80%;
    }
}

.ease-out-back {
    transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1);
}
</style>
