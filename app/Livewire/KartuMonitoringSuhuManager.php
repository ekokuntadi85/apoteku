<?php

namespace App\Livewire;

use App\Models\KartuMonitoringSuhu;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Title;

#[Title('Kartu Monitoring Suhu')]
class KartuMonitoringSuhuManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $isUpdateMode = false;
    public $kartuMonitoringSuhuId;
    public $suhu_ruangan, $suhu_pendingin, $waktu_pengukuran;
    public $selectedMonth;

    protected function rules()
    {
        return [
            'suhu_ruangan' => 'required|numeric',
            'suhu_pendingin' => 'required|numeric',
            'waktu_pengukuran' => 'nullable|date',
        ];
    }

    public function mount()
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function render()
    {
        $kartuMonitoringSuhus = KartuMonitoringSuhu::with('user')
                                            ->whereYear('waktu_pengukuran', 
                                            Carbon
                                            ::parse($this->selectedMonth)->year)
                                            ->whereMonth('waktu_pengukuran', 
                                            Carbon
                                            ::parse($this->selectedMonth)->month)
                                            ->latest()->paginate(10);

        $averageSuhuRuangan = $kartuMonitoringSuhus->avg('suhu_ruangan');
        $averageSuhuPendingin = $kartuMonitoringSuhus->avg('suhu_pendingin');

        return view('livewire.kartu-monitoring-suhu-manager', [
            'kartuMonitoringSuhus' => $kartuMonitoringSuhus,
            'averageSuhuRuangan' => $averageSuhuRuangan,
            'averageSuhuPendingin' => $averageSuhuPendingin,
        ]);
    }

    public function create()
    {
        $this->isUpdateMode = false;
        $this->resetInputFields();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $kartuMonitoringSuhu = KartuMonitoringSuhu::findOrFail($id);
        $this->kartuMonitoringSuhuId = $id;
        $this->suhu_ruangan = $kartuMonitoringSuhu->suhu_ruangan;
        $this->suhu_pendingin = $kartuMonitoringSuhu->suhu_pendingin;
        $this->waktu_pengukuran = \Carbon\Carbon::parse($kartuMonitoringSuhu->waktu_pengukuran)->format('Y-m-d\TH:i');
        $this->isUpdateMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        KartuMonitoringSuhu::updateOrCreate(['id' => $this->kartuMonitoringSuhuId], [
            'suhu_ruangan' => $this->suhu_ruangan,
            'suhu_pendingin' => $this->suhu_pendingin,
            'waktu_pengukuran' => $this->waktu_pengukuran ?: now(),
            'user_id' => auth()->id(),
        ]);

        session()->flash('message', 
            $this->isUpdateMode ? 'Data berhasil diupdate.' : 'Data berhasil ditambahkan.');

        $this->closeModal();
    }

    public function delete($id)
    {
        KartuMonitoringSuhu::find($id)->delete();
        session()->flash('message', 'Data berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    public function printCard()
    {
        $this->dispatch('open-new-tab', url: route('kartu-monitoring-suhu.print', ['month' => $this->selectedMonth]));
    }

    private function resetInputFields()
    {
        $this->kartuMonitoringSuhuId = null;
        $this->suhu_ruangan = '';
        $this->suhu_pendingin = '';
        $this->waktu_pengukuran = '';
        $this->resetErrorBag();
    }
}
