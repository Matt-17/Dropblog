using Microsoft.UI.Xaml;
using Windows.ApplicationModel.Activation;
using Windows.ApplicationModel.DataTransfer.ShareTarget;
using Windows.ApplicationModel.DataTransfer;

namespace Dropblog.Platforms.Windows;

public static class ShareTargetHandler
{
    public static async Task HandleActivationAsync(IActivatedEventArgs args)
    {
        if (args is ShareTargetActivatedEventArgs shareArgs)
        {
            await HandleShareTargetActivationAsync(shareArgs);
        }
    }

    public static async Task<bool> HandleShareTargetActivationAsync(ShareTargetActivatedEventArgs args)
    {
        try
        {
            var shareOperation = args.ShareOperation;
            var data = shareOperation.Data;

            string? title = null;
            string? text = null;
            string? uri = null;

            // Get title
            if (data.Properties.Title is not null)
            {
                title = data.Properties.Title;
            }

            // Get text content
            if (data.Contains(StandardDataFormats.Text))
            {
                text = await data.GetTextAsync();
            }

            // Get URI
            if (data.Contains(StandardDataFormats.WebLink))
            {
                var webLink = await data.GetWebLinkAsync();
                uri = webLink?.ToString();
            }

            // Get application link (for sharing from apps)
            if (data.Contains(StandardDataFormats.ApplicationLink))
            {
                var appLink = await data.GetApplicationLinkAsync();
                uri ??= appLink?.ToString();
            }

            // Store the shared content for the main app to pick up
            var preferences = Microsoft.Maui.Storage.Preferences.Default;
            preferences.Set("ShareTarget_HasContent", true);
            preferences.Set("ShareTarget_Title", title ?? "");
            preferences.Set("ShareTarget_Text", text ?? "");
            preferences.Set("ShareTarget_Uri", uri ?? "");
            preferences.Set("ShareTarget_Timestamp", DateTimeOffset.UtcNow.ToUnixTimeSeconds());

            // Report that the share operation was successful
            shareOperation.ReportCompleted();

            return true;
        }
        catch
        {
            return false;
        }
    }
} 