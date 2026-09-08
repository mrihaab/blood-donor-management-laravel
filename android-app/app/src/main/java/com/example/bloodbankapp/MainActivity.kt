package com.example.bloodbankapp

import android.annotation.SuppressLint
import android.net.Uri
import android.net.http.SslError
import android.os.Build
import android.os.Bundle
import android.webkit.CookieManager
import android.webkit.SslErrorHandler
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.ComponentActivity
import androidx.activity.OnBackPressedCallback

class MainActivity : ComponentActivity() {
    private lateinit var webView: WebView

    companion object {
        // Base Hospital Portal URL (Configurable via Build Type / Remote Config)
        const val BASE_HOSPITAL_URL = "https://bloodbank-clinical-portal.loca.lt/hospital/dashboard"
        const val APPROVED_HOST = "bloodbank-clinical-portal.loca.lt"
    }

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        webView = WebView(this)
        setContentView(webView)

        val settings = webView.settings
        
        // Capabilities required by Laravel & Alpine.js UI
        settings.javaScriptEnabled = true
        settings.domStorageEnabled = true
        settings.useWideViewPort = true
        settings.loadWithOverviewMode = true

        // Security Hardening Rules
        settings.databaseEnabled = false
        settings.allowFileAccess = false
        settings.allowContentAccess = false
        settings.mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
        settings.userAgentString = settings.userAgentString + " BloodBankApp/1.0 HospitalNativeShell"

        // Disable WebView debugging in production builds
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.KITKAT) {
            WebView.setWebContentsDebuggingEnabled(false)
        }

        // Cookie persistence configuration
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(webView, false)

        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url?.toString() ?: return true
                return handleUrlNavigation(view, url)
            }

            @Deprecated("Deprecated in API 24")
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                if (url == null) return true
                return handleUrlNavigation(view, url)
            }

            override fun onReceivedSslError(view: WebView?, handler: SslErrorHandler?, error: SslError?) {
                // Strictly cancel SSL navigation on certificate errors
                handler?.cancel()
            }

            private fun handleUrlNavigation(view: WebView?, url: String): Boolean {
                val uri = Uri.parse(url)

                // 1. Enforce HTTPS scheme
                if (uri.scheme != "https") {
                    return true // Block non-HTTPS cleartext traffic
                }

                // 2. Enforce Approved Hostname
                if (uri.host != APPROVED_HOST) {
                    return true // Block external untrusted domains
                }

                // 3. Defense-in-depth: Deny navigation to Admin or Donor portals
                val path = uri.path ?: ""
                if (path.startsWith("/admin") || path.startsWith("/donor")) {
                    return true // Block access to unauthorized portal routes
                }

                view?.loadUrl(url)
                return false
            }
        }

        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (::webView.isInitialized && webView.canGoBack()) {
                    webView.goBack()
                } else {
                    finish()
                }
            }
        })

        webView.loadUrl(BASE_HOSPITAL_URL)
    }
}
