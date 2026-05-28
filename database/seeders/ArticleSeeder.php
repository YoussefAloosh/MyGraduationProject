<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $creator   = User::where('email', 'creator@admin.com')->first();
        $moderator = User::where('email', 'moderator@admin.com')->first();
        $admin     = User::where('email', 'admin@admin.com')->first();
        $trusted   = User::where('email', 'trusted@test.com')->first();
        $member    = User::where('email', 'member@test.com')->first();

        $articles = [
            // ─── Approved ─────────────────────────────────────────
            [
                'user_id'      => $creator?->id ?? 1,
                'title'        => 'كيف تتصرف عند حدوث زلزال؟',
                'content'      => 'الزلزال من أخطر الكوارث الطبيعية. في هذا المقال نستعرض الخطوات الأساسية التي يجب اتباعها لحماية نفسك وعائلتك: أولاً ابق هادئاً وانحنِ تحت طاولة صلبة أو قرب جدار داخلي بعيداً عن النوافذ. ابتعد عن الأثاث الثقيل والمرايا. وعندما يتوقف الاهتزاز اخرج بهدوء من المبنى باستخدام الدرج لا المصعد.',
                'status'       => 'approved',
                'published_at' => now()->subDays(10),
            ],
            [
                'user_id'      => $moderator?->id ?? 1,
                'title'        => 'الإسعافات الأولية في حالات الحرائق',
                'content'      => 'عند التعرض لحريق يجب المبادرة بإخراج المصابين من المنطقة المشتعلة بعيداً عن مصادر الدخان. في حالة الحروق: برّد المنطقة المحروقة بماء بارد (ليس مثلجاً) لمدة 10 دقائق. لا تكسر الفقاعات ولا تضع معجون الأسنان أو الزيت. لف المنطقة بضمادة نظيفة وانتقل فوراً للمستشفى.',
                'status'       => 'approved',
                'published_at' => now()->subDays(8),
            ],
            [
                'user_id'      => $admin?->id ?? 1,
                'title'        => 'دليل الطوارئ الشامل: ماذا تضع في حقيبة الطوارئ؟',
                'content'      => 'حقيبة الطوارئ هي أساس الاستعداد لأي كارثة. يجب أن تحتوي على: مياه (لترين لكل شخص يومياً لمدة 3 أيام)، طعام غير قابل للتلف، مصباح يدوي وبطاريات احتياطية، صافرة، قناع غبار، بطانية حرارية، مجموعة إسعافات أولية، نسخ من الوثائق الهامة، كمية كافية من الأدوية الموصوفة.',
                'status'       => 'approved',
                'published_at' => now()->subDays(6),
            ],
            [
                'user_id'      => $trusted?->id ?? 1,
                'title'        => 'كيف تطلب المساعدة بفعالية في حالات الطوارئ؟',
                'content'      => 'عند الاتصال بالطوارئ أو استخدام تطبيق الطوارئ كن محدداً: أذكر موقعك بدقة، وصف طبيعة الحالة (حريق، حادث، طبي)، عدد المصابين، وأي معلومات إضافية تساعد فرق الإنقاذ. لا تقطع الاتصال حتى يطلب منك المشغل ذلك.',
                'status'       => 'approved',
                'published_at' => now()->subDays(4),
            ],
            [
                'user_id'      => $creator?->id ?? 1,
                'title'        => 'الوقاية من الغرق: نصائح للسلامة المائية',
                'content'      => 'الغرق هو أحد أكثر حوادث الطوارئ شيوعاً. إليك أهم النصائح: تعلم السباحة، لا تسبح وحدك، ارتدِ سترة النجاة في الأنشطة المائية، تعلم كيفية إنقاذ الغريق دون تعريض نفسك للخطر (ارمِ حبلاً أو عوامة بدلاً من الدخول للماء).',
                'status'       => 'approved',
                'published_at' => now()->subDays(2),
            ],
            [
                'user_id'      => $moderator?->id ?? 1,
                'title'        => 'التعامل مع إصابات الحوادث المرورية',
                'content'      => 'عند وقوع حادث مروري: أولاً أوقف المركبة بأمان وشغّل أضواء الطوارئ. تحقق من سلامتك ثم سلامة الآخرين. اتصل بالإسعاف وأخبرهم بعدد المصابين وطبيعة الإصابات. لا تحرك المصاب إلا إذا كان في خطر مباشر (كحريق). إذا كان المصاب لا يتنفس ابدأ الإنعاش القلبي الرئوي.',
                'status'       => 'approved',
                'published_at' => now()->subDays(1),
            ],
            [
                'user_id'      => $admin?->id ?? 1,
                'title'        => 'كيف تنشئ خطة إخلاء لمنزلك؟',
                'content'      => 'خطة الإخلاء تنقذ أرواحاً. ارسم مخطط منزلك وحدد مخرجَين على الأقل من كل غرفة. حدد نقطة تجمع خارج المنزل يعرفها جميع أفراد الأسرة. تدرب مع العائلة على الخطة مرتين في السنة. تأكد أن كل فرد يعرف كيف يتصل بالطوارئ وما هو رقم المسعف.',
                'status'       => 'approved',
                'published_at' => now()->subHours(5),
            ],

            // ─── Pending (لم تُقبل بعد) ───────────────────────────
            [
                'user_id'      => $member?->id ?? 1,
                'title'        => 'تجربتي مع الفيضانات في منطقتنا',
                'content'      => 'في عام 2024 شهدت منطقتنا فيضانات غير مسبوقة. في هذا المقال أشارك تجربتي الشخصية وما تعلمته من هذه الكارثة وكيف ساعدنا بعضنا كمجتمع.',
                'status'       => 'pending',
                'published_at' => null,
            ],
            [
                'user_id'      => $trusted?->id ?? 1,
                'title'        => 'دور الشباب في الاستجابة للكوارث',
                'content'      => 'الشباب يمثلون طاقة هائلة يمكن توظيفها في مجال الإغاثة والطوارئ. نستعرض في هذا المقال كيف يمكن للشباب المساهمة الفعالة في فرق الاستجابة المجتمعية.',
                'status'       => 'pending',
                'published_at' => null,
            ],

            // ─── Rejected ──────────────────────────────────────────
            [
                'user_id'      => $member?->id ?? 1,
                'title'        => 'مقال مرفوض - محتوى غير ملائم',
                'content'      => 'محتوى تجريبي مرفوض.',
                'status'       => 'rejected',
                'published_at' => null,
            ],
        ];

        foreach ($articles as $data) {
            if (Article::where('title', $data['title'])->exists()) {
                continue;
            }
            $base = Str::slug($data['title']) ?: 'article';
            Article::create(array_merge($data, ['slug' => $base . '-' . uniqid()]));
        }

        // ─── Add sample comments & reactions ──────────────────────────────────
        $approvedArticles = Article::approved()->get();
        $users = User::whereIn('email', [
            'admin@admin.com', 'moderator@admin.com', 'creator@admin.com',
            'trusted@test.com', 'member@test.com', 'ahmad@test.com', 'lina@test.com',
        ])->get();

        foreach ($approvedArticles->take(4) as $article) {
            foreach ($users->take(3) as $user) {
                Comment::firstOrCreate(
                    ['commentable_id' => $article->id, 'commentable_type' => 'App\\Models\\Article', 'user_id' => $user->id],
                    [
                        'content'     => fake()->sentences(2, true),
                        'is_weighted' => $user->hasAnyRole(['trusted', 'creator', 'moderator', 'admin']),
                    ]
                );

                Reaction::firstOrCreate(
                    ['reactionable_id' => $article->id, 'reactionable_type' => 'App\\Models\\Article', 'user_id' => $user->id],
                    ['type' => fake()->randomElement(['like', 'dislike'])]
                );
            }
        }

        $this->command->info('✅ Articles, Comments & Reactions seeded (' . count($articles) . ' articles)');
    }
}
