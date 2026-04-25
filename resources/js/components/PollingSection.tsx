import React, { useState } from 'react';

interface PollOption {
  id: number;
  label: string;
  votes: number;
}

/**
 * PollingSection - Simple polling widget dengan progress bar
 * Placeholder dengan mock data
 */
export const PollingSection: React.FC = () => {
  const [hasVoted, setHasVoted] = useState(false);
  const [selectedOption, setSelectedOption] = useState<number | null>(null);

  const mockQuestion = 'Program apa dari IKA UNIMED yang paling ingin Anda ikuti tahun ini?';
  const mockOptions: PollOption[] = [
    { id: 1, label: 'Program Pengembangan Karier Alumni', votes: 245 },
    { id: 2, label: 'Kegiatan Sosial & Pengabdian Masyarakat', votes: 187 },
    { id: 3, label: 'Networking & Reuni Alumni', votes: 89 },
    { id: 4, label: 'Beasiswa & Dukungan Pendidikan', votes: 34 },
  ];

  const totalVotes = mockOptions.reduce((sum, opt) => sum + opt.votes, 0);

  const handleVote = (optionId: number) => {
    setSelectedOption(optionId);
    setHasVoted(true);
    // API call would go here
  };

  return (
    <div className="bg-white border border-[#E6EAE8] rounded-lg p-6 mb-8">
      {/* Header */}
      <div className="mb-6 pb-4 border-b border-[#E6EAE8]">
        <h3 className="text-lg font-bold text-[#0F172A] flex items-center gap-2">
          📊 Suara Alumni IKA UNIMED
        </h3>
      </div>

      {/* Question */}
      <h4 className="font-semibold text-[#0F172A] mb-6 text-base">
        {mockQuestion}
      </h4>

      {/* Options */}
      <div className="space-y-3">
        {mockOptions.map((option) => {
          const percentage = totalVotes > 0 ? (option.votes / totalVotes) * 100 : 0;
          const isSelected = selectedOption === option.id;

          return (
            <div key={option.id}>
              <button
                onClick={() => handleVote(option.id)}
                className={`w-full text-left p-3 rounded-lg border transition-all ${
                  isSelected
                    ? 'border-[#0F766E] bg-[#0F766E]/5'
                    : 'border-[#E6EAE8] hover:border-[#0F766E] hover:bg-[#F8FAF9]'
                }`}
                disabled={hasVoted}
              >
                <div className="relative h-10 flex items-center">
                  {/* Progress bar background */}
                  <div
                    className="absolute left-0 top-0 bottom-0 bg-[#0F766E]/10 rounded transition-all"
                    style={{ width: hasVoted ? `${percentage}%` : '0%' }}
                  />

                  {/* Content */}
                  <div className="relative flex-1 flex items-center justify-between">
                    <span className="font-medium text-[#0F172A]">{option.label}</span>
                    {hasVoted && (
                      <span className="text-sm text-[#6B7280]">
                        {percentage.toFixed(1)}% ({option.votes})
                      </span>
                    )}
                  </div>
                </div>
              </button>
            </div>
          );
        })}
      </div>

      {/* Footer */}
      <div className="mt-6 pt-4 border-t border-[#E6EAE8] text-center">
        <p className="text-sm text-[#6B7280]">
          Total: {totalVotes} suara alumni
        </p>
      </div>
    </div>
  );
};

export default PollingSection;
