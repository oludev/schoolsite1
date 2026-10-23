<?php
/* The application form as a printable page or Word-compatible download. Blank before it is filled in. */
function application_doc_html(?array $pay, ?array $a, string $mode = 'print'): string {
    $school = e(setting('school_name', 'Wineridge Academy')); $lg = logo_path();
    $v = fn($k) => $a ? e($a[$k] ?? '') : '';
    $row = fn($label, $html) => '<tr><th>' . $label . '</th><td>' . ($html !== '' ? $html : '<span class="blank"></span>') . '</td></tr>';
    $img = fn($f) => $f ? '<img src="' . e(abs_url('uploads/' . $f)) . '" alt="">' : '';
    $dob = ($a && $a['dob']) ? date('j F Y', strtotime($a['dob'])) : '';
    $signed = ($a && $a['signed_on']) ? date('j F Y', strtotime($a['signed_on'])) : '';
    $css = '@page{size:A4;margin:14mm}body{font:13px/1.5 Arial,Helvetica,sans-serif;color:#1F2D3D;margin:0;background:#fff}.sheet{max-width:790px;margin:0 auto;padding:18px}'
        . 'table{width:100%;border-collapse:collapse}.hd td{vertical-align:top}.hd{border-bottom:4px solid #D4AF37;margin-bottom:12px}.hd h1{font:700 22px Georgia,serif;color:#102A43;margin:0}.hd p{margin:2px 0;color:#52667A}'
        . '.ph{width:130px;text-align:right}.photo{width:116px;height:146px;border:1.5px solid #102A43;display:inline-block;text-align:center;font-size:11px;color:#52667A;overflow:hidden}.photo img{width:116px;height:146px;object-fit:cover}'
        . 'h2{background:#102A43;color:#fff;font:700 13px Arial;margin:14px 0 0;padding:6px 10px;border-bottom:3px solid #D4AF37}'
        . '.f th{width:34%;text-align:left;font-weight:600;background:#F3F6FA;border:1px solid #C3CFDB;padding:6px 8px}.f td{border:1px solid #C3CFDB;padding:6px 8px}'
        . '.blank{display:inline-block;min-width:230px;border-bottom:1px dotted #52667A;height:14px}.sig img{height:60px}.small{font-size:11px;color:#52667A}'
        . '.office{border:2px solid #102A43;margin-top:16px;padding:0 10px 10px}.office h2{margin:0 -10px 8px}.office td{padding:8px 6px;border:0}.line{border-bottom:1px solid #1F2D3D;height:22px}'
        . '.bar{background:#0B1F33;padding:10px;text-align:center}.bar a,.bar button{background:#D4AF37;color:#0B1F33;border:0;padding:9px 18px;border-radius:6px;font:700 13px Arial;text-decoration:none;cursor:pointer;margin:0 4px}'
        . '@media print{.bar{display:none}}';
    $bar = $mode === 'print' ? '<div class="bar"><button onclick="window.print()">Print / Save as PDF</button></div>' : '';
    $paid = ($pay && $pay['status'] === 'paid') ? ($pay['method'] === 'free' ? 'No application fee.' : 'Application fee ' . e(money($pay['amount'])) . ' paid on ' . date('j M Y', strtotime($pay['paid_at'])) . ', payment reference ' . e($pay['ref']) . '.') : '';
    $h = '<!DOCTYPE html>' . ($mode === 'doc' ? '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">' : '<html lang="en">')
        . '<head><meta charset="utf-8"><title>Application form</title><style>' . $css . '</style></head><body>' . $bar . '<div class="sheet">'
        . '<table class="hd"><tr><td>' . ($lg ? '<img src="' . e(abs_url($lg)) . '" alt="" style="height:54px;margin-bottom:6px"><br>' : '') . '<h1>' . $school . '</h1><p>' . e(setting('address', '')) . '</p><p>' . e(setting('phone', '')) . ' &middot; ' . e(setting('email', '')) . '</p><p><strong>APPLICATION FOR ADMISSION</strong>' . ($a ? ' &middot; No. ' . e($a['ref_no']) : '') . '</p></td>'
        . '<td class="ph"><span class="photo">' . ($a && $a['passport_photo'] ? $img($a['passport_photo']) : '<br><br><br>Passport<br>photograph') . '</span></td></tr></table>'
        . '<h2>A. STUDENT\'S DETAILS</h2><table class="f">'
        . $row('Surname', $v('surname')) . $row('First name', $v('first_name')) . $row('Other names', $v('other_names')) . $row('Date of birth', e($dob)) . $row('Gender', $v('gender'))
        . $row('Nationality', $v('nationality')) . $row('State of origin', $v('state_origin')) . $row('Religion', $v('religion')) . $row('Section applied for', $v('section')) . $row('Class applied for', $v('class_applied'))
        . $row('Previous school', $v('previous_school')) . $row('Last class attended', $v('previous_class')) . $row('Blood group / Genotype', trim($v('blood_group') . ' ' . ($a && $a['genotype'] ? '/ ' . $v('genotype') : ''))) . $row('Medical conditions or allergies', $v('medical_notes'))
        . '</table><h2>B. PARENT OR GUARDIAN</h2><table class="f">'
        . $row('Full name', $v('parent_name')) . $row('Relationship to student', $v('relationship')) . $row('Occupation', $v('occupation')) . $row('Home address', $v('address')) . $row('Telephone', $v('phone')) . $row('Alternative telephone', $v('alt_phone'))
        . $row('Email address', $v('email')) . $row('Emergency contact (name and phone)', trim($v('emergency_name') . ' ' . $v('emergency_phone')))
        . '</table><h2>C. DECLARATION</h2><p>I declare that the information given on this form is true and complete. I agree to abide by the rules and regulations of the school if admission is offered.</p>'
        . '<table class="f"><tr><th>Parent or guardian\'s signature</th><td class="sig">' . ($a && $a['signature_img'] ? $img($a['signature_img']) : '<span class="blank"></span>') . '</td></tr>'
        . $row('Name', $v('signed_name')) . $row('Date', e($signed)) . '</table>'
        . '<div class="office"><h2>FOR SCHOOL USE ONLY</h2><table><tr><td style="width:34%">Headteacher (Primary): name</td><td><div class="line"></div></td></tr><tr><td>Signature</td><td><div class="line"></div></td></tr><tr><td>Date</td><td><div class="line"></div></td></tr>'
        . '<tr><td colspan="2"></td></tr><tr><td>Principal (Secondary): name</td><td><div class="line"></div></td></tr><tr><td>Signature</td><td><div class="line"></div></td></tr><tr><td>Date</td><td><div class="line"></div></td></tr>'
        . '<tr><td>Decision</td><td>&#9744; Admitted &nbsp; &#9744; Waiting list &nbsp; &#9744; Not offered a place</td></tr></table></div>'
        . '<p class="small" style="margin-top:12px">' . $paid . '</p></div></body></html>';
    return $h;
}
