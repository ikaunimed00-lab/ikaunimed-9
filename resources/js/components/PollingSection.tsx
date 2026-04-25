import React, { useState } from 'react';

interface PollOption {
  id: number;
  label: string;
  votes: number;
}

interface PollData {
  id: number;
  question: string;
  options: PollOption[];
  total_votes: number;
  closes_at?: string | null;
}

interface PollingSectionProps {
  poll?: PollData | null;
}

/**
 * PollingSection - widget polling alumni.
 *
 * Kontrak data (TODO Fase 2):
 *   prop `poll` berisi { id, question, options[{id,label,votes}], total_votes,
 *   closes_at }. Ketika prop `poll` tidak dikirim (modul polling belum
 *   tersedia), komponen menampilkan placeholder ramah — BUKAN poll mock dengan
 *   suara fiktif agar tidak menyesatkan pembaca / Google AdSense reviewer.
 *
 * Begitu modul polling siap (model + endpoint vote + cache), controller bisa
 * mengirim props ini tanpa mengubah layout halaman.
 */
export const PollingSection: React.FC<PollingSectionProps> = ({ poll = null }) => {
  const [hasVoted, setHasVoted] = useState(false);
  const [selectedOption, setSelectedOption] = useState<number | null>(null);

  if (!poll) {
    return (
      <div className="bg-white border border-dashed border-[#CBD5E1] rounded-lg p-6 text-center">
        <div className="w-14 h-14 mx-auto mb-3 rounded-full bg-[#F0FDFA] flex items-center justify-center text-2xl">
          📊
        </div>
        <h3 className="text-base font-bold text-[#0F172A] mb-1">
          Polling alumni segera hadir
        </h3>
        <p className="text-sm text-[#6B7280] max-w-md mx-auto">
          Redaksi sedang menyiapkan polling resmi IKA UNIMED. Pertanyaan dan
          pilihan jawaban akan ditampilkan di sini segera setelah dipublikasikan.
        </p>
      </div>
    );
  }

  const totalVotes = poll.total_votes
    || poll.options.reduce((sum, opt) => sum + (opt.votes ?? 0), 0);

  const handleVote = (optionId: number) => {
    setSelectedOption(optionId);
    setHasVoted(true);
    // TODO Fase 2: panggil endpoint POST /api/polls/{poll.id}/vote.
  };

  return (
    <div className="bg-white border border-[#E6EAE8] rounded-lg p-6 mb-8">
      <div className="mb-6 pb-4 border-b border-[#E6EAE8]">
        <h3 className="text-lg font-bold text-[#0F172A] flex items-center gap-2">
          📊 Suara Alumni IKA UNIMED
        </h3>
      </div>

      <h4 className="font-semibold text-[#0F172A] mb-6 text-base">
        {poll.question}
      </h4>

      <div className="space-y-3">
        {poll.options.map((option) => {
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
                  <div
                    className="absolute left-0 top-0 bottom-0 bg-[#0F766E]/10 rounded transition-all"
                    style={{ width: hasVoted ? `${percentage}%` : '0%' }}
                  />
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

      <div className="mt-6 pt-4 border-t border-[#E6EAE8] text-center">
        <p className="text-sm text-[#6B7280]">
          Total: {totalVotes} suara alumni
          {poll.closes_at && (
            <>
              {' • '}
              Berakhir{' '}
              {new Date(poll.closes_at).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
              })}
            </>
          )}
        </p>
      </div>
    </div>
  );
};

export default PollingSection;
