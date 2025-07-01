#!/bin/bash

# Test script to validate the website-links workflow

echo "🔍 Testing Website Links Workflow"
echo "================================="

# Check if the workflow file exists and is valid YAML
if [ -f ".github/workflows/website-links.yml" ]; then
    echo "✅ Workflow file exists"
    
    # Check if python3 is available
    if command -v python3 &> /dev/null; then
        echo "✅ Python3 is available"
        
        # Try to parse YAML (basic check)
        if python3 -c "import yaml; yaml.safe_load(open('.github/workflows/website-links.yml'))" 2>/dev/null; then
            echo "✅ Workflow YAML is valid"
        else
            echo "❌ Workflow YAML is invalid"
            exit 1
        fi
    else
        echo "❌ Python3 not found"
        exit 1
    fi
    
    # Check for required components
    if grep -q "ScholliYT/Broken-Links-Crawler-Action" .github/workflows/website-links.yml; then
        echo "✅ Uses broken link crawler action"
    else
        echo "❌ Missing broken link crawler action"
        exit 1
    fi
    
    if grep -q "requests" .github/workflows/website-links.yml; then
        echo "✅ Installs requests library"
    else
        echo "❌ Missing requests library installation"
        exit 1
    fi
    
    if grep -q "gh pr create" .github/workflows/website-links.yml; then
        echo "✅ Creates pull requests"
    else
        echo "❌ Missing PR creation"
        exit 1
    fi
    
    # Check schedule
    if grep -q "cron:" .github/workflows/website-links.yml; then
        echo "✅ Has scheduled execution"
    else
        echo "❌ Missing scheduled execution"
        exit 1
    fi
    
    echo ""
    echo "🎉 All checks passed! The workflow is ready."
    echo ""
    echo "Next steps:"
    echo "1. Commit and push the workflow"
    echo "2. Test manually via workflow dispatch"
    echo "3. Monitor the weekly runs"
    
else
    echo "❌ Workflow file not found"
    exit 1
fi
