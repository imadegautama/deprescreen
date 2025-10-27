import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import type { Symptom } from '@/types';
import ScreeningForm from '@/components/Screening/ScreeningForm';
import ScreeningHeader from '@/components/Screening/ScreeningHeader';
import ProgressBar from '@/components/Screening/ProgressBar';
import WelcomeModal from '@/components/Screening/WelcomeModal';

interface Props {
    symptoms: Symptom[];
}

export default function ScreeningIndex({ symptoms }: Props) {
    const [answers, setAnswers] = useState<Record<string, number>>({});
    const [loading, setLoading] = useState(false);
    const [showWelcome, setShowWelcome] = useState(true);
    const [currentStep, setCurrentStep] = useState(0);

    // Initialize answers with default values (0) keyed by symptom_id
    useEffect(() => {
        const initialAnswers: Record<string, number> = {};
        // Initialize answers keyed by symptom_id (from database)
        symptoms.forEach((symptom) => {
            initialAnswers[symptom.symptom_id] = 0;
        });
        setAnswers(initialAnswers);
    }, [symptoms]);

    const handleAnswerChange = (symptomId: string, value: number) => {
        setAnswers((prev) => ({
            ...prev,
            [symptomId]: value,
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        // Transform answers to required format for backend
        // Format: [{ "symptom_id": 1, "value": 0 }, { "symptom_id": 2, "value": 1 }, ...]
        const answersArray = Object.entries(answers).map(([symptomId, value]) => ({
            symptom_id: symptomId,
            value: value
        }));

        console.log('Submitting answers:', answersArray);

        router.post(
            '/screening',
            { answers: answersArray },
            {
                onError: (errors) => {
                    console.error('Form errors:', errors);
                    setLoading(false);
                },
                onFinish: () => setLoading(false),
            }
        );
    };

    const totalAnswered = Object.values(answers).filter((v) => v !== 0).length;
    const progressPercentage = (totalAnswered / symptoms.length) * 100;

    const handleStep = (step: number) => {
        setCurrentStep(step);
    };

    return (
        <>
            <Head title="Screening Depresi" />

            {showWelcome && (
                <WelcomeModal
                    onClose={() => setShowWelcome(false)}
                    totalSymptoms={symptoms.length}
                />
            )}

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-8 px-4 sm:px-6 lg:px-8">
                <div className="max-w-4xl mx-auto">
                    {/* Header */}
                    <ScreeningHeader />

                    {/* Progress Bar */}
                    <div className="mb-8">
                        <ProgressBar
                            current={totalAnswered}
                            total={symptoms.length}
                            percentage={progressPercentage}
                        />
                    </div>

                    {/* Form */}
                    <ScreeningForm
                        symptoms={symptoms}
                        answers={answers}
                        onAnswerChange={handleAnswerChange}
                        onSubmit={handleSubmit}
                        loading={loading}
                        onStepChange={handleStep}
                        currentStep={currentStep}
                    />

                    {/* Info Box */}
                    <div className="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p className="text-sm text-blue-800">
                            <span className="font-semibold">💡 Info:</span> Screening ini akan membantu
                            mendeteksi tanda-tanda depresi. Hasil bukan diagnosis medis resmi.
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
