#!/bin/bash

# Add Android SDK to PATH
export ANDROID_HOME=$HOME/Library/Android/sdk
export PATH=$PATH:$ANDROID_HOME/emulator
export PATH=$PATH:$ANDROID_HOME/platform-tools
export PATH=$PATH:$ANDROID_HOME/tools
export PATH=$PATH:$ANDROID_HOME/tools/bin

# Install Android SDK Build-tools 35.0.0
echo "Installing Android SDK Build-tools 35.0.0..."
$ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager "build-tools;35.0.0" || echo "Failed to install build-tools"

# Accept licenses
yes | $ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager --licenses

echo "Android setup complete!"
echo ""
echo "Add these lines to your ~/.zshrc or ~/.bash_profile:"
echo ""
echo "export ANDROID_HOME=\$HOME/Library/Android/sdk"
echo "export PATH=\$PATH:\$ANDROID_HOME/emulator"
echo "export PATH=\$PATH:\$ANDROID_HOME/platform-tools"
echo "export PATH=\$PATH:\$ANDROID_HOME/tools"
echo "export PATH=\$PATH:\$ANDROID_HOME/tools/bin"