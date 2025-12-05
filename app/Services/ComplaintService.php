<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class ComplaintService
{
    public function submitComplaint(array $data, int $userId, ?UploadedFile $attachment): Complaint
    {
        DB::beginTransaction();
        $attachmentPath = null;

        try {

            if ($attachment) {
                // حفظ الملف في مجلد 'complaints/attachments' في قرص 'public'
                $attachmentPath = $attachment->store('complaints/attachments', 'public');
            }

            $complaint = Complaint::create([
                'user_id' => $userId,
                'government_agencie_id' => $data['government_agencie_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'attachment_path' => $attachmentPath,
                'status' => 'Pending',
            ]);

            DB::commit();
            return $complaint;

        } catch (Exception $e) {
            DB::rollBack();
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            throw new Exception('فشل في إرسال الشكوى. يرجى المحاولة لاحقاً: ' . $e->getMessage());
        }
    }
}
