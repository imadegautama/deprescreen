import React from 'react';

interface ScreeningHeaderProps {
    subtitle?: string;
}

export default function ScreeningHeader({ subtitle }: ScreeningHeaderProps) {
    return (
        <div className="text-center mb-8">
            <h1 className="text-4xl font-bold text-gray-900 mb-2">Asesmen Depresi</h1>
            <p className="text-lg text-gray-600">
                {subtitle || 'Screening kesehatan mental untuk deteksi dini depresi'}
            </p>
        </div>
    );
}
