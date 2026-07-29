<script setup>
import { useRouter } from "vue-router";
import {
    LogIn,
    FileText,
    CheckCircle,
    ClipboardList,
    Package,
    ShieldCheck,
    Clock,
    FileCheck,
    BarChart3,
    Lock,
} from "lucide-vue-next";
import GuestLayout from "@/components/layout/GuestLayout.vue";
import BaseCard from "@/components/ui/BaseCard.vue";
import BaseButton from "@/components/ui/BaseButton.vue";

const router = useRouter();

const navigateToLogin = () => {
    router.push({ name: "Login" });
};
</script>

<template>
    <section class="relative py-20 overflow-hidden">
        <div
            class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-slate-50"
        ></div>
        <div class="relative container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1
                    class="text-5xl md:text-6xl font-bold text-slate-900 leading-tight"
                >
                    Inventory Management System
                </h1>
                <p class="mt-6 text-xl text-slate-600 leading-relaxed">
                    Streamline your inventory workflow with seamless
                    collaboration between warehouse staff and management. Track
                    every movement, approve every change, and maintain complete
                    transparency with automated audit trails.
                </p>
                <div
                    class="mt-10 flex flex-col sm:flex-row gap-4 justify-center"
                >
                    <BaseButton
                        @click="navigateToLogin"
                        variant="primary"
                        size="lg"
                    >
                        <LogIn :size="20" />
                        Sign In to System
                    </BaseButton>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                    How It Works
                </h2>
                <p class="mt-4 text-lg text-slate-600">
                    Three simple steps to manage your inventory with complete
                    accountability
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <BaseCard :padding="true" :shadow="true" class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 mb-4"
                    >
                        <FileText :size="32" class="text-primary-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">
                        Staff Records Mutations
                    </h3>
                    <p class="text-slate-600">
                        Warehouse staff logs incoming and outgoing inventory
                        with supporting documentation. Every entry includes item
                        details, quantities, and PDF attachments for
                        verification.
                    </p>
                </BaseCard>

                <BaseCard :padding="true" :shadow="true" class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success-100 mb-4"
                    >
                        <CheckCircle :size="32" class="text-success-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">
                        Users Reviews & Approves
                    </h3>
                    <p class="text-slate-600">
                        Users review submitted mutations, verify
                        documentation, and approve or reject changes. Every
                        decision is recorded with timestamps and optional
                        rejection reasons.
                    </p>
                </BaseCard>

                <BaseCard :padding="true" :shadow="true" class="text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-info-100 mb-4"
                    >
                        <ClipboardList :size="32" class="text-info-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">
                        Audit Trail Preserved
                    </h3>
                    <p class="text-slate-600">
                        Complete history of changes is automatically recorded.
                        Track who did what, when, and why. Immutable records
                        ensure compliance and accountability across all
                        operations.
                    </p>
                </BaseCard>
            </div>
        </div>
    </section>

    <section class="py-16 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                    Powerful Features
                </h2>
                <p class="mt-4 text-lg text-slate-600">
                    Everything you need to manage inventory with confidence and
                    clarity
                </p>
            </div>
            <div
                class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto"
            >
                <BaseCard :padding="true" :shadow="true">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-primary-100"
                            >
                                <Package :size="24" class="text-primary-600" />
                            </div>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 mb-2"
                            >
                                Master Item Management
                            </h3>
                            <p class="text-slate-600">
                                Organize inventory with detailed item records
                                including codes, categories, units, and storage
                                locations. Track stock levels with visual
                                indicators for low inventory.
                            </p>
                        </div>
                    </div>
                </BaseCard>

                <BaseCard :padding="true" :shadow="true">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-success-100"
                            >
                                <ShieldCheck
                                    :size="24"
                                    class="text-success-600"
                                />
                            </div>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 mb-2"
                            >
                                Approval Workflow
                            </h3>
                            <p class="text-slate-600">
                                Users propose changes, managers approve. Clear
                                separation of duties ensures accuracy and
                                prevents unauthorized modifications to inventory
                                records.
                            </p>
                        </div>
                    </div>
                </BaseCard>

                <BaseCard :padding="true" :shadow="true">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-info-100"
                            >
                                <Clock :size="24" class="text-info-600" />
                            </div>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 mb-2"
                            >
                                Automatic Audit Trails
                            </h3>
                            <p class="text-slate-600">
                                Every action is logged automatically with user,
                                timestamp, and change details. Complete
                                transparency and accountability without manual
                                record-keeping.
                            </p>
                        </div>
                    </div>
                </BaseCard>

                <BaseCard :padding="true" :shadow="true">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-warning-100"
                            >
                                <FileCheck
                                    :size="24"
                                    class="text-warning-600"
                                />
                            </div>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 mb-2"
                            >
                                Document Management
                            </h3>
                            <p class="text-slate-600">
                                Attach PDF documents to every mutation with
                                built-in validation. Store delivery notes,
                                invoices, and authorization forms securely with
                                size and format controls.
                            </p>
                        </div>
                    </div>
                </BaseCard>

                <BaseCard :padding="true" :shadow="true">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-danger-100"
                            >
                                <BarChart3 :size="24" class="text-danger-600" />
                            </div>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 mb-2"
                            >
                                Export & Import with Background Jobs
                            </h3>
                            <p class="text-slate-600">
                                Handle large Excel exports and imports
                                efficiently with background job processing.
                                Queue heavy operations to avoid blocking, with
                                progress tracking and notifications.
                            </p>
                        </div>
                    </div>
                </BaseCard>

                <BaseCard :padding="true" :shadow="true">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-info-100"
                            >
                                <Lock :size="24" class="text-info-600" />
                            </div>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 mb-2"
                            >
                                Immutable Transaction History
                            </h3>
                            <p class="text-slate-600">
                                Once approved, transactions cannot be modified
                                or deleted. Maintain data integrity with
                                immutable snapshots that preserve historical
                                accuracy for auditing.
                            </p>
                        </div>
                    </div>
                </BaseCard>
            </div>
        </div>
    </section>
    <footer class="py-6 text-center text-sm text-slate-600">
        <p>
            &copy; {{ new Date().getFullYear() }} Inventory Management System.
            All rights reserved.
        </p>
    </footer>
</template>
