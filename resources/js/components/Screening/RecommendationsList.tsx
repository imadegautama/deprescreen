import React from 'react';
import { Recommendation } from '@/types';

interface Props {
    recommendations: Recommendation[];
}

const getRecommendationIcon = (type: string) => {
    const icons: Record<string, string> = {
        professional: '👨‍⚕️',
        self_care: '🧘',
        lifestyle: '🏃',
        therapy: '💬',
        emergency: '🚑',
        resources: '📚',
    };
    return icons[type] || '💡';
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        high: 'border-red-300 bg-red-50',
        medium: 'border-yellow-300 bg-yellow-50',
        low: 'border-blue-300 bg-blue-50',
    };
    return colors[priority] || colors.low;
};

const getPriorityBadge = (priority: string) => {
    const badges: Record<string, string> = {
        high: 'bg-red-100 text-red-800',
        medium: 'bg-yellow-100 text-yellow-800',
        low: 'bg-blue-100 text-blue-800',
    };
    return badges[priority] || badges.low;
};

export default function RecommendationsList({ recommendations }: Props) {
    if (!recommendations || recommendations.length === 0) {
        return null;
    }

    const highPriority = recommendations.filter((r) => r.priority === 'high');
    const mediumPriority = recommendations.filter((r) => r.priority === 'medium');
    const lowPriority = recommendations.filter((r) => r.priority === 'low');

    return (
        <div className="mb-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">Rekomendasi & Saran</h2>

            {/* High Priority */}
            {highPriority.length > 0 && (
                <div className="mb-6">
                    <h3 className="font-semibold text-red-700 mb-3">🔴 Prioritas Tinggi</h3>
                    <div className="space-y-3">
                        {highPriority.map((rec, idx) => (
                            <div key={idx} className={`p-4 rounded-lg border-2 ${getPriorityColor(rec.priority)}`}>
                                <div className="flex items-start gap-3">
                                    <span className="text-2xl flex-shrink-0">{getRecommendationIcon(rec.type)}</span>
                                    <div className="flex-1">
                                        <h4 className="font-semibold text-gray-900">{rec.title}</h4>
                                        <p className="text-gray-700 text-sm mt-1">{rec.description}</p>
                                        {rec.action_url && (
                                            <a
                                                href={rec.action_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-red-600 hover:text-red-700 text-sm font-medium mt-2 inline-block"
                                            >
                                                Pelajari lebih lanjut →
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Medium Priority */}
            {mediumPriority.length > 0 && (
                <div className="mb-6">
                    <h3 className="font-semibold text-yellow-700 mb-3">🟡 Prioritas Sedang</h3>
                    <div className="space-y-3">
                        {mediumPriority.map((rec, idx) => (
                            <div key={idx} className={`p-4 rounded-lg border-2 ${getPriorityColor(rec.priority)}`}>
                                <div className="flex items-start gap-3">
                                    <span className="text-2xl flex-shrink-0">{getRecommendationIcon(rec.type)}</span>
                                    <div className="flex-1">
                                        <h4 className="font-semibold text-gray-900">{rec.title}</h4>
                                        <p className="text-gray-700 text-sm mt-1">{rec.description}</p>
                                        {rec.action_url && (
                                            <a
                                                href={rec.action_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-yellow-600 hover:text-yellow-700 text-sm font-medium mt-2 inline-block"
                                            >
                                                Pelajari lebih lanjut →
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Low Priority */}
            {lowPriority.length > 0 && (
                <div>
                    <h3 className="font-semibold text-blue-700 mb-3">🔵 Prioritas Rendah</h3>
                    <div className="space-y-3">
                        {lowPriority.map((rec, idx) => (
                            <div key={idx} className={`p-4 rounded-lg border-2 ${getPriorityColor(rec.priority)}`}>
                                <div className="flex items-start gap-3">
                                    <span className="text-2xl flex-shrink-0">{getRecommendationIcon(rec.type)}</span>
                                    <div className="flex-1">
                                        <h4 className="font-semibold text-gray-900">{rec.title}</h4>
                                        <p className="text-gray-700 text-sm mt-1">{rec.description}</p>
                                        {rec.action_url && (
                                            <a
                                                href={rec.action_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-blue-600 hover:text-blue-700 text-sm font-medium mt-2 inline-block"
                                            >
                                                Pelajari lebih lanjut →
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
