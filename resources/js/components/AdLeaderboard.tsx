import React from 'react';

interface AdLeaderboardProps {
    className?: string;
}

const AdLeaderboard: React.FC<AdLeaderboardProps> = ({ className = '' }) => {
    return (
        <div className={`w-full bg-[#F8FAF9] border-b border-[#E6EAE8] py-4 ${className}`}>
            <div className="mx-auto px-4 sm:px-6 lg:px-8 max-w-[1440px]">
                <div className="flex flex-col items-center justify-center">
                    <div className="text-[10px] uppercase tracking-wider text-[#6B7280] mb-1">Advertisement</div>
                    <div className="w-full max-w-[970px] h-[90px] bg-white border border-dashed border-[#CBD5E1] rounded flex items-center justify-center overflow-hidden relative">
                         {/* Placeholder for AdScript */}
                         <span className="text-sm text-[#94A3B8] font-medium">Leaderboard Ad (970x90)</span>
                         <div id="ad-leaderboard-slot" className="absolute inset-0"></div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdLeaderboard;
