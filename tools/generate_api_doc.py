# -*- coding: utf-8 -*-
"""Generate App API reference Word document (OOXML) without external deps."""
import zipfile
from pathlib import Path
from xml.sax.saxutils import escape

OUTPUT = Path(__file__).resolve().parents[2] / "docs" / "API_Web_Flutter.docx"

SECTIONS = [
    ("منصة الاستجابة المجتمعية — API المرجعي (الويب + Flutter)", "title"),
    ("Base URL: /api  |  Auth: Bearer Token  |  الموثّق: roles.contains('trusted')", "meta"),
    ("ملاحظة: Flutter لا يدعم Guest — يتطلب تسجيل دخول.", "note"),
    ("", "blank"),
    ("1) Guest — زائر (ويب فقط)", "h1"),
    ("GET /articles — عرض قائمة المقالات المعتمدة", "row"),
    ("GET /articles/{id} — عرض تفاصيل مقال", "row"),
    ("GET /articles/{id}/comments — قراءة تعليقات المقال", "row"),
    ("", "blank"),
    ("2) Auth — التسجيل والدخول", "h1"),
    ("POST /auth/register — إنشاء حساب وإرسال OTP | أي شخص جديد", "row"),
    ("POST /auth/verify-code — تأكيد OTP → حساب + token + دور member | بعد التسجيل", "row"),
    ("POST /auth/resend — إعادة إرسال OTP | أثناء التسجيل", "row"),
    ("POST /auth/login — تسجيل دخول → token + roles + permissions | Registered / Trusted", "row"),
    ("POST /auth/logout — تسجيل خروج | Registered / Trusted", "row"),
    ("GET /auth/me — جلب بيانات المستخدم الحالي | Registered / Trusted", "row"),
    ("POST /auth/password/forgot — طلب OTP لاستعادة كلمة المرور | Registered / Trusted", "row"),
    ("POST /auth/password/verify — التحقق من OTP الاستعادة | Registered / Trusted", "row"),
    ("POST /auth/password/reset — تعيين كلمة مرور جديدة | Registered / Trusted", "row"),
    ("", "blank"),
    ("3) Registered — مسجل (member) — Token مطلوب", "h1"),
    ("POST /articles/{id}/comments — إضافة تعليق على مقال | ✅ موجود", "row"),
    ("DELETE /comments/{id} — حذف تعليق (حسب الصلاحية) | ✅ موجود", "row"),
    ("POST /reactions — Like أو Dislike على مقال/تعليق | ✅ موجود", "row"),
    ("", "blank"),
    ("4) Trusted — موثّق (trusted) — Token + دور trusted", "h1"),
    ("— مقالات (قادم)", "h2"),
    ("POST /articles — إنشاء مقال | 🔜 قادم", "row"),
    ("PUT /articles/{id} — تعديل مقال | 🔜 قادم", "row"),
    ("— طوارئ (موجود)", "h2"),
    ("POST /emergency/sos — إرسال SOS وإنشاء حالة طوارئ | ✅ موجود", "row"),
    ("POST /emergency/cases/{id}/retry — إعادة إبلاغ (حد أقصى مرتين) | ✅ موجود", "row"),
    ("— طوارئ (قادم)", "h2"),
    ("POST /emergency/profile/home-location — حفظ الموقع الدائم والانضمام لغروب", "row"),
    ("GET /emergency/my-group — جلب الغروب الحالي وعضويتي", "row"),
    ("GET /emergency/notifications — إشعارات الطوارئ الواردة", "row"),
    ("POST /emergency/notifications/{id}/respond — قبول أو رفض إشعار", "row"),
    ("GET /emergency/cases/{id} — تفاصيل حالة طوارئ", "row"),
    ("POST /emergency/cases/{id}/resolve — إغلاق الحالة", "row"),
    ("GET /emergency/groups/{id}/chat — قراءة محادثة الغروب", "row"),
    ("POST /emergency/groups/{id}/chat — إرسال رسالة", "row"),
    ("POST /emergency/ratings — تقييم عضو (+ / −)", "row"),
    ("POST /emergency/reports — إبلاغ (spam / حالة كاذبة / …)", "row"),
    ("POST /role-requests — طلب دور (مثل منقذ)", "row"),
    ("— أقسام أخرى (قادم)", "h2"),
    ("الكورسات — استعراض الكورسات وروابطها", "row"),
    ("المتجر — استعراض المتاجر والمنتجات", "row"),
    ("الخدمات — استعراض الجهات والخدمات", "row"),
    ("", "blank"),
    ("ملخص سريع", "h1"),
    ("Guest: قراءة مقالات + تعليقات (ويب فقط)", "row"),
    ("Registered (member): تعليق + Like/Dislike", "row"),
    ("Trusted: كل Registered + الطوارئ + باقي الأقسام (تدريجياً)", "row"),
    ("", "blank"),
    ("حسابات تجريبية (Password: password)", "h1"),
    ("Registered: member@test.com", "row"),
    ("Trusted: trusted@test.com أو lina@test.com", "row"),
]


def para(text: str, style: str) -> str:
    if style == "blank":
        return "<w:p/>"
    text = escape(text)
    rtl = ' w:bidi="1"' if any("\u0600" <= c <= "\u06FF" for c in text) else ""
    props = ""
    if style == "title":
        props = (
            '<w:pPr><w:jc w:val="center"/><w:bidi/><w:rPr>'
            '<w:b/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr></w:pPr>'
        )
    elif style == "h1":
        props = (
            '<w:pPr><w:bidi/><w:rPr><w:b/><w:sz w:val="28"/>'
            '<w:szCs w:val="28"/></w:rPr></w:pPr>'
        )
    elif style == "h2":
        props = (
            '<w:pPr><w:bidi/><w:rPr><w:b/><w:sz w:val="24"/>'
            '<w:szCs w:val="24"/></w:rPr></w:pPr>'
        )
    elif style in ("meta", "note"):
        props = '<w:pPr><w:bidi/><w:rPr><w:i/><w:color w:val="444444"/></w:rPr></w:pPr>'
    else:
        props = '<w:pPr><w:bidi/></w:pPr>'
    return f"<w:p{rtl}>{props}<w:r><w:rPr><w:rtl/></w:rPr><w:t xml:space=\"preserve\">{text}</w:t></w:r></w:p>"


def build_document_xml() -> str:
    body = "".join(para(t, s) for t, s in SECTIONS)
    return (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
        'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        f"<w:body>{body}"
        '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
        '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
        "</w:body></w:document>"
    )


CONTENT_TYPES = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>"""

RELS = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>"""

DOC_RELS = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>"""


def main() -> None:
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(OUTPUT, "w", zipfile.ZIP_DEFLATED) as zf:
        zf.writestr("[Content_Types].xml", CONTENT_TYPES)
        zf.writestr("_rels/.rels", RELS)
        zf.writestr("word/document.xml", build_document_xml())
        zf.writestr("word/_rels/document.xml.rels", DOC_RELS)
    print(str(OUTPUT))


if __name__ == "__main__":
    main()
