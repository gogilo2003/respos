<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const features = [
    {
        title: 'QR Ordering',
        description:
            'Customers scan a QR code to view the menu and place orders instantly.',
    },
    {
        title: 'Fast Billing',
        description:
            'Cash-first billing with clear receipts and smooth checkout for staff.',
    },
    {
        title: 'Kitchen & Waiter Flow',
        description:
            'Orders move from QR → kitchen → payment with fewer handoffs.',
    },
];
</script>

<template>
    <Head title="About" />
    <WebLayout title="About ResPos">
        <div class="min-h-screen bg-white">
            <header class="bg-[#ffea95] py-16">
                <div class="mx-auto max-w-7xl px-4">
                    <h1 class="text-4xl font-extrabold text-black">
                        About ResPos
                    </h1>
                    <p
                        class="mt-3 max-w-2xl text-sm text-black/90 sm:text-base"
                    >
                        ResPos is a QR-based restaurant ordering and billing
                        system built to make operations simpler—for customers,
                        waiters, and the kitchen.
                    </p>
                </div>
            </header>

            <!-- cards -->

            <div class="card">
                <h2>Store Hours</h2>
                <p>
                    Monday – Friday: 8:00 AM – 9:00 PM<br />Saturday – Sunday:
                    9:00 AM – 10:00 PM
                </p>
            </div>

            <div class="card">
                <h2>Contact Us</h2>
                <p>
                    123 Food Street <br />Delicious City, State 12345
                    <br />Phone: (123) 456-7890 <br />Email: info@respos.com
                </p>

                <div class="social-icons">
                    <a
                        href="https://www.instagram.com/respos/"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <img src="" alt="Instagram icon" class="social-icon" />
                    </a>

                    <a
                        href="https://www.facebook.com/respos/"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <img
                            src="https://tse3.mm.bing.net/th/id/OIP.7Xv7HE8tc1bESoiRSrGm3gHaEl?cb=thfvnextfalcon4&rs=1&pid=ImgDetMain&o=7&rm=3"
                            alt="Facebook icon"
                            class="social-icon"
                        />
                    </a>
                </div>
            </div>

            <footer class="bg-[#ffea95] py-8">
                <div class="mx-auto max-w-7xl px-4">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="text-sm font-semibold text-black">
                            © {{ new Date().getFullYear() }} ResPos. All rights
                            reserved.
                        </div>
                        <div
                            class="flex items-center gap-6 text-sm text-black/90"
                        >
                            <Link
                                href="/welcome-categories"
                                class="hover:underline"
                            >
                                Food Menu
                            </Link>
                            <Link href="/" class="hover:underline">Home</Link>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </WebLayout>
</template>

<style scoped>
body {
    margin: 0;
    padding: 0;
    background-color: #1b1906;
    color: #d6c5c5;
}

.card {
    margin: 2rem auto 0;
    padding: 1.5rem;
    max-width: 28rem;
    background: rgba(17, 1, 1, 0.12);
    border: 1px solid rgba(19, 2, 2, 0.15);
    border-radius: 1rem;
    text-align: left;
}

.card h2,
.card h2 {
    margin: 0 0 0.75rem;
    font-size: 1.4rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.card p {
    margin: 0;
    line-height: 1.8;
    font-size: 1rem;
}
.social-icons {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 1rem;
    margin-top: 1.5rem;
}

.social-icons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.social-icon {
    width: 1.6rem;
    height: 1.6rem;
    object-fit: contain;
}

.social-icons a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: 999px;
    background-color: rgba(255, 255, 255, 0.15);
    color: #b3872a;
    text-decoration: none;
    transition:
        background-color 0.2s ease,
        color 0.2s ease;
}
</style>
