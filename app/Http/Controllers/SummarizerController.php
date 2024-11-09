<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SummarizerController extends Controller
{
    public function index()
    {
        return view('summarizer');
    }

    public function summarize(Request $request)
    {
        $text = $request->input('text');
        $summary = $this->summarizeText($text);
        return view('summarizer', compact('text', 'summary'));
    }

    private function summarizeText($text)
    {
        // Daftar stop words
        $stopWords = ['yang', 'dan', 'atau', 'untuk', 'dari', 'dengan', 'ke', 'di', 'adalah', 'ini', 'itu'];

        // Pisahkan teks menjadi kalimat
        $sentences = preg_split('/(?<=[.?!])\s+/', $text);

        // Hitung frekuensi setiap kata
        $wordFrequency = $this->calculateWordFrequency($text, $stopWords);

        // Buat graf kalimat berdasarkan kesamaan kata-kata kunci
        $sentenceGraph = $this->buildSentenceGraph($sentences, $wordFrequency);

        // Jalankan algoritma PageRank-like untuk memberikan skor pada setiap kalimat
        $sentenceScores = $this->rankSentences($sentenceGraph);

        // Ambil kalimat dengan skor tertinggi (sekitar 30% dari total kalimat)
        arsort($sentenceScores);
        $selectedIndexes = array_slice(array_keys($sentenceScores), 0, round(count($sentenceScores) * 0.3));

        // Urutkan indeks berdasarkan urutan aslinya di dalam teks
        sort($selectedIndexes);

        // Gabungkan kalimat-kalimat yang dipilih menjadi ringkasan
        $summary = '';
        foreach ($selectedIndexes as $index) {
            $summary .= $sentences[$index] . ' ';
        }

        return trim($summary);
    }

    private function calculateWordFrequency($text, $stopWords)
    {
        $words = explode(' ', strtolower($text));
        $frequency = [];

        foreach ($words as $word) {
            $word = preg_replace('/[^\w]/', '', $word); // Hapus tanda baca
            if ($word && !in_array($word, $stopWords)) {
                if (isset($frequency[$word])) {
                    $frequency[$word]++;
                } else {
                    $frequency[$word] = 1;
                }
            }
        }

        return $frequency;
    }

    private function buildSentenceGraph($sentences, $wordFrequency)
    {
        $graph = [];
        $sentenceVectors = [];

        // Hitung vektor kata untuk setiap kalimat berdasarkan kata kunci
        foreach ($sentences as $index => $sentence) {
            $words = explode(' ', strtolower($sentence));
            $sentenceVectors[$index] = [];

            foreach ($words as $word) {
                $word = preg_replace('/[^\w]/', '', $word);
                if (isset($wordFrequency[$word])) {
                    $sentenceVectors[$index][$word] = $wordFrequency[$word];
                }
            }
        }

        // Buat graf kalimat dengan menghitung kesamaan kosinus antar kalimat
        foreach ($sentenceVectors as $i => $vectorA) {
            foreach ($sentenceVectors as $j => $vectorB) {
                if ($i != $j) {
                    $similarity = $this->cosineSimilarity($vectorA, $vectorB);
                    if ($similarity > 0) {
                        $graph[$i][$j] = $similarity;
                    }
                }
            }
        }

        return $graph;
    }

    private function cosineSimilarity($vectorA, $vectorB)
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        foreach ($vectorA as $key => $value) {
            $dotProduct += $value * ($vectorB[$key] ?? 0);
            $magnitudeA += pow($value, 2);
        }

        foreach ($vectorB as $value) {
            $magnitudeB += pow($value, 2);
        }

        $magnitude = sqrt($magnitudeA) * sqrt($magnitudeB);

        return $magnitude ? $dotProduct / $magnitude : 0;
    }

    private function rankSentences($graph)
    {
        $dampingFactor = 0.85;
        $convergenceThreshold = 0.0001;
        $maxIterations = 100;

        $sentenceScores = array_fill_keys(array_keys($graph), 1);

        for ($i = 0; $i < $maxIterations; $i++) {
            $newScores = $sentenceScores;
            $maxChange = 0;

            foreach ($graph as $sentence => $edges) {
                $newScore = (1 - $dampingFactor);

                foreach ($edges as $neighbor => $weight) {
                    $newScore += $dampingFactor * $weight * ($sentenceScores[$neighbor] / array_sum($graph[$neighbor]));
                }

                $maxChange = max($maxChange, abs($newScores[$sentence] - $newScore));
                $newScores[$sentence] = $newScore;
            }

            $sentenceScores = $newScores;

            if ($maxChange < $convergenceThreshold) {
                break;
            }
        }

        return $sentenceScores;
    }
}
