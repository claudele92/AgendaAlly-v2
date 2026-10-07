<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Shop;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Seeder;
use Str;

class BlogStorySeeder extends Seeder
{
    /**
     * 3 example blog posts and a handful of example stories - neither had
     * any seed data at all, so /blogs and the homepage's "stories" widget
     * were empty on every fresh install. Posts deliberately span the
     * platform's full category range (beauty/wellness plus the newer
     * Tailoring/Dental Care/Healthcare/Handyman/Laundry/Home Cleaning
     * categories), not beauty-only, so the blog reads as covering
     * everything AgendaAlly actually books.
     *
     * Stories are genuinely ephemeral by design (StoryRepository::list()
     * only returns rows from the last 24 hours), so these exist to
     * demonstrate the feature works end to end on a fresh install, not as
     * permanent content - they age out on their own, same as a real
     * seller's stories would.
     *
     * @return void
     */
    public function run(): void
    {
        if (!Blog::exists()) {
            $author = User::where('email', 'owner@githubit.com')->value('id') ?? User::query()->value('id');

            $posts = [
                [
                    'img' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1200&h=600&q=80',
                    'title' => '5 Signs It\'s Time to Book That Appointment You\'ve Been Putting Off',
                    'short_desc' => 'From haircuts to home repairs, here\'s how to tell you\'ve waited long enough.',
                    'description' => "<p>We've all done it - pushed a booking to \"next week\" more times than we'd like to admit. Whether it's a haircut that's grown out, a leaky faucet, or a dental check-up you keep postponing, here are five signs it's time to stop waiting.</p><h2>1. You've rescheduled it in your head at least three times</h2><p>If you keep telling yourself \"I'll book it tomorrow,\" that's usually a sign tomorrow needs to actually arrive.</p><h2>2. A small problem is starting to feel like a big one</h2><p>A small stain becomes a permanent one. A minor ache becomes a persistent one. Booking early is almost always cheaper and easier than booking late.</p><h2>3. You've mentioned it to three different people</h2><p>If you've complained about it that many times, you've already done the hard part - deciding it matters. Now just open the app.</p><h2>4. You know exactly who you'd book, if you booked</h2><p>Half the decision is already made. Finish the other half.</p><h2>5. It would only take five minutes</h2><p>Browsing real availability and booking on AgendaAlly usually takes less time than reading this list did.</p>",
                ],
                [
                    'img' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1200&h=600&q=80',
                    'title' => 'Beyond Beauty: Everything Else You Can Book on AgendaAlly',
                    'short_desc' => 'Tailoring, dental care, healthcare, handyman work, laundry, home cleaning - one app, every appointment.',
                    'description' => "<p>AgendaAlly started with beauty and wellness bookings, but the platform now covers a much wider range of everyday services. Here's a quick tour of what else you can book.</p><h2>Tailoring</h2><p>Alterations, custom fittings, and repairs from local tailors who know how to make something fit right.</p><h2>Dental Care</h2><p>Check-ups, cleanings, and consultations with dental professionals near you - no phone tag required.</p><h2>Healthcare</h2><p>General consultations and routine care from local healthcare providers, booked the same way you'd book a haircut.</p><h2>Handyman Services</h2><p>Repairs, installations, and small home projects handled by vetted local professionals.</p><h2>Laundry & Dry Cleaning</h2><p>Pickup and drop-off scheduling with local laundry and dry-cleaning services.</p><h2>Home Cleaning</h2><p>One-time or recurring cleaning appointments, on your schedule.</p><p>One app, one login, every appointment - that's the idea.</p>",
                ],
                [
                    'img' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&h=600&q=80',
                    'title' => 'How to Get the Most Out of Your Next Salon Visit',
                    'short_desc' => 'A few simple habits that make every appointment go smoother, for you and your master.',
                    'description' => "<p>A great appointment isn't just about who you book - it's also about how you show up. A few habits that make a real difference:</p><h2>Book with enough lead time</h2><p>Popular masters fill up. If you have a date in mind (an event, a trip), book a few days ahead rather than the night before.</p><h2>Say what you actually want, clearly</h2><p>\"Something like this photo\" beats \"just a trim, but different\" every time. Bring a reference if you have one.</p><h2>Confirm your booking details</h2><p>Double-check the service, time, and location in your confirmation - it takes ten seconds and avoids a wasted trip.</p><h2>Leave a review afterward</h2><p>Reviews help other customers choose well, and they help good masters get the recognition (and bookings) they deserve.</p>",
                ],
            ];

            foreach ($posts as $post) {
                $blog = Blog::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $author,
                    'type' => 1,
                    'active' => true,
                    'published_at' => now(),
                    'img' => $post['img'],
                ]);

                $blog->translations()->create([
                    'locale' => 'en',
                    'title' => $post['title'],
                    'short_desc' => $post['short_desc'],
                    'description' => $post['description'],
                ]);
            }
        }

        if (!Story::exists()) {
            $shopIds = Shop::query()->orderBy('id')->limit(3)->pluck('id');

            $storyImages = [
                'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&h=1400&q=80',
                'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=800&h=1400&q=80',
                'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&h=1400&q=80',
            ];

            foreach ($shopIds as $index => $shopId) {
                Story::create([
                    'shop_id' => $shopId,
                    'model_type' => Shop::class,
                    'model_id' => $shopId,
                    'file_urls' => [$storyImages[$index] ?? $storyImages[0]],
                    'active' => true,
                ]);
            }
        }
    }
}
