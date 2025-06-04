using Android;
using Android.App;
using Android.Content;
using Android.Content.PM;
using Android.OS;
using AndroidX.AppCompat.App;
using Dropblog.Services;

namespace Dropblog.Platforms.Android;

[Activity(
    Label = "Share to Dropblog",
    Theme = "@android:style/Theme.NoDisplay",
    LaunchMode = LaunchMode.SingleTop,
    Exported = true)]
[IntentFilter(
    new[] { Intent.ActionSend },
    Categories = new[] { Intent.CategoryDefault },
    DataMimeType = "text/plain")]
[IntentFilter(
    new[] { Intent.ActionSend },
    Categories = new[] { Intent.CategoryDefault },
    DataMimeType = "*/*")]
public class ShareTargetActivity : AppCompatActivity
{
    protected override void OnCreate(Bundle? savedInstanceState)
    {
        base.OnCreate(savedInstanceState);
        
        HandleIncomingShare();
        
        // Close this activity and return to main app
        Finish();
    }

    private void HandleIncomingShare()
    {
        var intent = Intent;
        
        if (intent?.Action == Intent.ActionSend)
        {
            var sharedText = intent.GetStringExtra(Intent.ExtraText);
            var sharedSubject = intent.GetStringExtra(Intent.ExtraSubject);
            var sharedTitle = intent.GetStringExtra(Intent.ExtraTitle);
            
            // Try to extract URL from text if present
            string? sharedUri = null;
            if (!string.IsNullOrWhiteSpace(sharedText))
            {
                // Simple URL detection
                var urlMatch = System.Text.RegularExpressions.Regex.Match(
                    sharedText, 
                    @"https?://[^\s]+", 
                    System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                
                if (urlMatch.Success)
                {
                    sharedUri = urlMatch.Value;
                }
            }

            // Store shared content for the main app to pick up
            var preferences = Microsoft.Maui.Storage.Preferences.Default;
            preferences.Set("ShareTarget_HasContent", true);
            preferences.Set("ShareTarget_Title", sharedTitle ?? sharedSubject ?? "");
            preferences.Set("ShareTarget_Text", sharedText ?? "");
            preferences.Set("ShareTarget_Uri", sharedUri ?? "");
            preferences.Set("ShareTarget_Timestamp", DateTimeOffset.UtcNow.ToUnixTimeSeconds());

            // Launch or bring to front the main app
            var mainIntent = new Intent(this, typeof(MainActivity));
            mainIntent.SetFlags(ActivityFlags.NewTask | ActivityFlags.ClearTop);
            mainIntent.PutExtra("FromShareTarget", true);
            StartActivity(mainIntent);
        }
    }
} 