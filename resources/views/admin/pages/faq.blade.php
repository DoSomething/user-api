@extends('admin.layouts.main')

@section('header_content')
    @include('admin.layouts.header', ['title' => 'FAQ'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h3>What is Northstar Admin?</h3>
                <p>Northstar Admin is an internal tool where staff and interns review and view more information about campaign actions. It is the tool you should use when looking for content for a sponsor, decks, media, etc!</p>

                <p>Additionally, Northstar Admin is the source of truth for all member activity (signups, posts, impact) and campaign/action metadata. Member activity from Northstar Admin is sent to Looker and powers all campaign dashboards.</p>

                <h3>There's a lot of terms flying around, can I get some definitions?</h3>
                <ul class='list'>
                    <li><b>Signup:</b> A signup is created in two ways. The first way is when a user signs up for a campaign. The second way is when a user completes an action that automatically creates the sign up. For example, on the ungated Prom Map experience, if the user signs the petition or submits a story, when the post is submitted, a signup is also made at the same time. Text posts over SMS are another example of when the signup is created at the same time as the post.</li>
                    <li><b>Why participated:</b> The "why participated" is the reason a user submits explaining why they took action in the campaign. A user can only have one "why participated" for the campaign, which means that while a user may submit multiple posts for a campaign, they only have one "why participated." The rationale behind this was that they're saying why they participated in the overall campaign, not why they decided to take and upload that photo or tip. If a user submits a new "why participated" their previous "why participated" is replaced with this new submission.</li>
                    <li><b>Post:</b> A post is created every time a user submits proof that they took action on a campaign. A user can submit multiple posts for one campaign. For example, a user signs up for Teens for Jeans and submits 5 photos. The user has 1 signup, 5 posts, and 1 reportback. We have many different types of posts, including: text posts, photo posts, call posts, and voter registration posts.</li>
                    <li><b>Quantity:</b> This is the total number of X a user did (i.e. how many items collected). There is one quantity for a member for a given campaign. If on the first submission they tell us they did 50 and then in the second submission they tell us 200, their total quantity is 250. We know both the quantity per submission and the total quantity for the campaign.</li>
                    <li><b>Reportback:</b> This is an internal term. If a member submits proof of their action (a post), they have reported back. If a member sends in multiple proofs of their action (posts), this is still just one reportback.</li>
                </ul>

                <p>Here's a visualization of how things get into the system and are structured:</p>
                <img src="https://images.ctfassets.net/81iqaqpfd8fy/6hAMqF2PfcFX9p2GICR87F/c73dc36d02d74839bc814488d789d84e/Rogue_RB_Flow_Diagram.jpg" alt="Rogue RB flow diagram"/>

                <h3>Why are tags important?</h3>
                <p>They're important because they allow admins to search for posts that fit a certain criteria and they allow us to identify trends, i.e., a lot of people are putting in unrealistic quantities or aren't completing the action which could be a result of unclear website instructions.</p>

                <h3 id="tags">What do the different tags mean?</h3>

                    <p>After selecting a status (Approve or Reject), you are able to apply tags. All of these tags were created from campaign, marketing or business development requests. <b>It's important to add tags as you're reviewing because other people can filter by tags on the Campaign Filtering page to find what they're looking for!</b></p>

                    <p>When you tag something as "Good for X" it will show up in the #badass-members Slack channel.</p>

                    <p><h4>Good Submission:</h4> Use this if the overall submission is good. <b>Note:</b> <em>we are running a test starting in June 2019 where users who are opted into the badge experiment will get a "Staff Fave" badge in their user profile if their submission has been tagged "Good Submission." You don't need to do anything special for this as this will happen automatically in the experiment.</em></p>

                    <p><h4>Good Quote:</h4> Use this if the text is good.</p>

                    <p><h4>Hide in Gallery: <em>Hides submission from the gallery, does NOT exclude the impact</em></h4>Sometimes the submission will accurately reflect the CTA and have a realistic impact, but you might not want to show it in the public, web gallery! Use the 'Hide in Gallery' for various reasons, including:</p>
                    <ul class='list'>
                        <li>The submission is not clear but you want to count the impact</li>
                        <li>The submission does not fully reflect the CTA but you want to count the impact</li>
                        <li>You want to count the impact, but the caption may include language or personal information about a user (i.e. email address) that shouldn't be shown in the public gallery</li>
                    </ul>

                    <p><h4>Good for Sponsor:</h4> Use this if the submission meets any asks the sponsor has made. For example, if Company A wants to see people outside only, you can use this tag on submissions where people are outside.</p>

                    <p><h4>Good for Storytelling:</h4> A tag request from the Impact team - these are submissions that show good impact and member activity. The Impact team will use this tag when looking for things for the Gala or other decks. This is overall a better submission than what you would tag as "Good Submission." You will likely use this one sparingly.</p>

                    <p><h4>Irrelevant:</h4> Use this if the submission has nothing to do with the campaign (i.e. a cat photo for Teens for Jeans).</p>

                    <p><h4>Inappropriate:</h4> Use this if there is inappropriate content (i.e. middle finger in the background of the image). If you're Accepting the submission, remember to use "Hide in Gallery" so that it doesn't show up in the public web gallery.</p>

                    <p><h4>Unrealistic Quantity:</h4> Use this if someone has put a ridiculously high number in the quantity field (i.e. they said they created 1 million Love Letters and it’s a selfie). You can tag as unrealistic quantity, but you'd still want to update the quantity to correct it!</p>

                    <p><h4>Test:</h4> Use this if it’s a DS individual who’s submitting tests. (i.e., the quote or caption says "test" or the member email is @dosomething.org or @test.com)</p>

                    <p><h4>Incomplete Action:</h4> Use this if someone didn’t complete the action.</p>

                    <p><h4>Bulk:</h4> This is used when a developer automatically bulk approves submissions. This has happened in the past for text post campaigns.</p>

                    <p><h4>Good for Brand:</h4> Use this if the submission clearly highlights a brand or brands.</p>

                    <p><h4>Group Photo:</h4> Use this if it's a group photo.</p>

                    <p><h4>Social:</h4> Use this if submission is a screenshot of a guide/checklist shared on social media.</p>


                {{-- @TODO: Commenting the following sections out since it contains info and links for our old Rogue setup. Needs updating! --}}
                {{-- <h3>In Rogue, can I...</h3>
                <ul class="list">
                    <li>Upload a photo for a member?</li>
                    <li>Change the quantity for a member?</li>
                    <li>Search for a member?</li>
                    <li>Search posts by tag? By status? Both?</li>
                </ul>
                <p>Yes! You can do all of these things! To learn how to do any of the things above or other fun things, refer to the <a href="https://docs.google.com/document/u/1/d/1wpwwSWmYBIXwt5RKVCJkBZIMDXyZRza22527CW6eTdY/edit#heading=h.hg3m91ssxcr1" target="_blank">Rogue Admin 101 guide!</a></p>

                <h3>Documentation</h3>
                <ul class='list'>
                    <li><a href="https://docs.google.com/document/u/1/d/1wpwwSWmYBIXwt5RKVCJkBZIMDXyZRza22527CW6eTdY/edit#heading=h.hg3m91ssxcr1" target="_blank">Rogue Admin 101</a></li>
                    <li><a href="https://docs.google.com/document/d/1WVAQWk9d3G8VgZ-tOxQyWYYpM5E20C4VrUnzv1NUoHY/edit?ts=5755892b" target="_blank">Reportback Reviewing 101</a></li>
                    <li><a href="https://github.com/DoSomething/rogue/wiki" target="_blank">Rogue Technical Documentation</a></li>
                </ul> --}}

                <p>Got questions that aren't answered here or in the documentation linked above? Please ask in the <a href="https://dosomething.slack.com/archives/C09ANFQLA" target="_blank">#help-product</a> channel!</p>
            </div>
        </div>
    </div>
@stop

