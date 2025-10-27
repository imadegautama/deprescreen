import React from 'react';

interface ProgressBarProps {
    current: number;
    total: number;
    percentage: number;
}

export default function ProgressBar({ current, total, percentage }: ProgressBarProps) {
    return (
        <div className="w-full">
            <div className="flex items-center justify-between mb-2">
                <span className="text-sm font-medium text-gray-700">
                    Progress: {current} dari {total}
                </span>
                <span className="text-sm font-semibold text-indigo-600">{Math.round(percentage)}%</span>
            </div>
            <div className="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                    className="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 transition-all duration-300"
                    style={{ width: `${percentage}%` }}
                ></div>
            </div>
        </div>
    );
}
