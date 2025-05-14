# Comprehensive API Test for Franchisee Mobile App

This document explains how to use the comprehensive API testing functionality to validate all API endpoints are working correctly between the mobile app and the Laravel backend.

## Overview

The Comprehensive API Test is a powerful diagnostic tool that verifies all API endpoints needed by the franchisee mobile app. It:

1. Tests all critical franchisee API endpoints
2. Validates response data structure and content
3. Provides detailed error information when endpoints fail
4. Displays summaries of retrieved data
5. Allows sharing of detailed test reports

This is especially useful when:
- Setting up a new environment
- Debugging connectivity issues
- Verifying all functionality is available
- Testing after backend changes

## How to Access

You can access the Comprehensive API Test in two ways:

1. **From the Login Screen**: Tap the "Comprehensive API Test" button at the bottom of the login screen.
2. **From URL**: Use the navigation prop to navigate to 'ComprehensiveAPITest' from any screen.

## Using the Test Tool

### Prerequisites

Before running the test, ensure:

1. The Laravel backend is running and accessible
2. The database has been properly set up
3. Franchisee user accounts exist
4. The correct API Base URL is configured in the app

### Test Process

1. **Enter Credentials**:
   - Enter a valid franchisee email and password
   - These will be used for authentication with the backend

2. **Run the Test**:
   - Tap "Run Comprehensive API Test"
   - The app will test all endpoints in sequence
   - This may take 10-20 seconds depending on response times

3. **Review Results**:
   - A summary section shows passed/failed/skipped test counts
   - Each endpoint is listed with its success status
   - Tap any endpoint to see detailed results
   - Failed endpoints are automatically expanded

4. **Share Report**:
   - Tap "Share Detailed Report" to export and share a full report
   - This can be sent to developers or saved for troubleshooting

## Endpoints Tested

The comprehensive test validates these key functional areas:

### Authentication
- Login
- Token validation

### User Profile
- User profile data
- Franchisee address

### Product Catalog
- Product listings
- Product details
- Favorite toggling

### Cart
- View cart
- Add item to cart
- Update cart item
- Remove from cart

### Orders
- Pending orders
- Order history
- Order details

## Understanding Test Results

### Success Criteria

Each endpoint test checks for:

1. **HTTP Success**: The API responded with a 200-299 status code
2. **Content Type**: The response has the correct content-type (application/json)
3. **Expected Keys**: The response contains expected data structure keys
4. **Required Fields**: All required data fields are present

### Data Validation

For data-returning endpoints, the test checks for:
- The presence of expected data arrays (products, orders, etc.)
- Sample data to verify structure
- Count information where applicable
- Nested data structure conformity

### Common Failures

If tests fail, check for these common issues:

1. **Authentication Failures**:
   - Invalid credentials
   - Token generation issues
   - Permission problems

2. **Endpoint Not Found**:
   - Laravel routes not defined correctly
   - URL path differences between mobile and web 

3. **Data Format Issues**:
   - Laravel returning unexpected response formats
   - Missing expected data fields
   - Casing issues (camelCase vs snake_case)

4. **Server Errors**:
   - PHP/Laravel exceptions
   - Database connectivity issues
   - Resource limitations

## Troubleshooting

If multiple endpoints fail:
1. Check the Laravel API logs for errors
2. Verify the Base URL is correct
3. Ensure Laravel is properly configured for API responses
4. Confirm authentication is working correctly

If specific endpoints fail:
1. Check the specific error messages in the expanded view
2. Verify the endpoint exists in your Laravel routes
3. Test the endpoint directly using Postman or similar tools
4. Check permissions for that specific endpoint

## Extending the Tests

The test module at `/src/utils/FranchiseeApiTest.js` can be extended to add:
- New endpoints as they're added to the backend
- More detailed validation logic
- Custom test sequences
- Additional reporting features

For developers: The test uses the standard fetch API and follows the same authentication flow as the main app.

## Conclusion

The Comprehensive API Test tool helps bridge the gap between the mobile app and backend, ensuring all functionality is available and working correctly. Use it regularly during development and whenever API connectivity issues arise.

For further assistance, contact the development team or check the Laravel logs on the backend server.